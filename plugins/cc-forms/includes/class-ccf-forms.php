<?php
/**
 * Acceso a los datos: formularios, versiones del esquema, cupos y respuestas.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CCF_Forms {

	const DEFAULTS = [
		'description'  => '',
		'opens_at'     => '',      // 'Y-m-d H:i' hora de Santa Fe; vacío = abre al publicar.
		'closes_at'    => '',      // vacío = abierto hasta cerrarlo a mano.
		'quota'        => 0,       // 0 = sin cupo.
		'notify_mode'  => 'immediate', // immediate | none
		'notify_to'    => '',      // casillas del área, separadas por coma.
		'reply_to'     => '',      // casilla que contesta a quien se inscribe.
		'confirm_text' => 'Recibimos tu inscripción. El equipo se va a comunicar con vos.',
		'closed_text'  => 'La inscripción está cerrada. Gracias por tu interés.',
		'dedupe'       => '',      // '' | email | dni
		'header_id'    => 0,
		'header_mobile_id' => 0,
		'header_alt'   => '',
		'purpose'      => '',      // finalidad específica que se suma al aviso legal.
		'prefix'       => 'CC',    // del número de registro: CC-2026-000123.
		'responsables' => [],      // ids de usuarios que ven y exportan las respuestas.
		'summary_link' => '',      // enlace del botón del correo de confirmación.
	];

	private static function t( string $name ): string {
		return CCF_Install::table( $name );
	}

	private static function hydrate( ?object $row ): ?object {
		if ( ! $row ) {
			return null;
		}
		$row->id       = (int) $row->id;
		$row->settings = array_merge( self::DEFAULTS, (array) json_decode( $row->settings, true ) );
		$row->schema   = self::schema( (int) $row->version_id );
		return $row;
	}

	public static function get( int $id ): ?object {
		global $wpdb;
		return self::hydrate( $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::t( 'forms' ) . ' WHERE id = %d', $id ) ) );
	}

	public static function get_by_slug( string $slug ): ?object {
		global $wpdb;
		return self::hydrate( $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::t( 'forms' ) . ' WHERE slug = %s', $slug ) ) );
	}

	/** @return object[] */
	public static function all(): array {
		global $wpdb;
		$rows = $wpdb->get_results( 'SELECT * FROM ' . self::t( 'forms' ) . ' ORDER BY updated_at DESC' );
		return array_map( [ self::class, 'hydrate' ], $rows );
	}

	public static function schema( int $version_id ): ?array {
		global $wpdb;
		$json = $version_id ? $wpdb->get_var( $wpdb->prepare( 'SELECT schema_json FROM ' . self::t( 'form_versions' ) . ' WHERE id = %d', $version_id ) ) : null;
		return $json ? json_decode( $json, true ) : null;
	}

	/** Plantillas de forms/*.json: tipo => [title, settings, schema]. */
	public static function templates(): array {
		$out = [];
		foreach ( glob( CCF_DIR . 'forms/*.json' ) as $file ) {
			$out[ basename( $file, '.json' ) ] = json_decode( file_get_contents( $file ), true );
		}
		return $out;
	}

	public static function create( string $type ): int {
		global $wpdb;
		$tpl  = self::templates()[ $type ] ?? [ 'title' => 'Formulario nuevo', 'settings' => [], 'schema' => [ 'steps' => [ [ 'id' => 'datos', 'title' => 'Tus datos', 'fields' => [ [ 'id' => 'nombre', 'type' => 'text', 'label' => 'Apellido y nombre', 'required' => true ] ] ] ] ] ];
		$now  = current_time( 'mysql' );
		$slug = sanitize_title( $tpl['title'] );
		while ( self::get_by_slug( $slug ) ) {
			$slug .= '-2';
		}
		$wpdb->insert( self::t( 'forms' ), [
			'slug'       => $slug,
			'title'      => $tpl['title'],
			'type'       => $type,
			'status'     => 'draft',
			'settings'   => wp_json_encode( array_merge( self::DEFAULTS, $tpl['settings'] ?? [] ) ),
			'created_at' => $now,
			'updated_at' => $now,
		] );
		$id = (int) $wpdb->insert_id;
		$wpdb->insert( self::t( 'quotas' ), [ 'form_id' => $id, 'option_key' => '', 'capacity' => (int) ( $tpl['settings']['quota'] ?? 0 ), 'used' => 0 ] );
		self::new_version( $id, $tpl['schema'] + [ '_aviso' => self::legal_text( $tpl['settings'] ?? [] ) ] );
		CCF_Install::audit( 'crear_formulario', $id );
		return $id;
	}

	public static function new_version( int $form_id, array $schema ): int {
		global $wpdb;
		$wpdb->insert( self::t( 'form_versions' ), [
			'form_id'     => $form_id,
			'schema_json' => wp_json_encode( $schema, JSON_UNESCAPED_UNICODE ),
			'created_by'  => get_current_user_id(),
			'created_at'  => current_time( 'mysql' ),
		] );
		$version = (int) $wpdb->insert_id;
		$wpdb->update( self::t( 'forms' ), [ 'version_id' => $version ], [ 'id' => $form_id ] );
		return $version;
	}

	/** Guarda datos y ajustes; si el esquema cambió, crea una versión nueva y las respuestas viejas quedan con la suya. */
	public static function save( object $form, array $data, array $settings, array $schema ): void {
		global $wpdb;
		$schema['_aviso'] = self::legal_text( $settings );
		if ( wp_json_encode( $schema ) !== wp_json_encode( $form->schema ) ) {
			self::new_version( $form->id, $schema );
		}
		$wpdb->update( self::t( 'forms' ), [
			'title'      => $data['title'],
			'slug'       => $data['slug'],
			'status'     => $data['status'],
			'settings'   => wp_json_encode( array_merge( self::DEFAULTS, $settings ), JSON_UNESCAPED_UNICODE ),
			'updated_at' => current_time( 'mysql' ),
		], [ 'id' => $form->id ] );
		// El cupo usado se conserva; solo cambia la capacidad.
		$wpdb->query( $wpdb->prepare(
			'INSERT INTO ' . self::t( 'quotas' ) . " (form_id, option_key, capacity, used) VALUES (%d, '', %d, 0) ON DUPLICATE KEY UPDATE capacity = VALUES(capacity)",
			$form->id, (int) $settings['quota']
		) );
		CCF_Install::audit( 'editar_formulario', $form->id, 0, $data['status'] );
	}

	/**
	 * Aviso de la Ley 25.326: el texto base de los ajustes más la finalidad del formulario.
	 * Se copia en cada versión del esquema, así cada respuesta queda con el texto exacto que aceptó.
	 */
	public static function legal_text( array $settings ): string {
		return trim( get_option( 'ccf_legal_text', '' ) . ' ' . ( $settings['purpose'] ?? '' ) );
	}

	public static function quota_used( int $form_id ): int {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT used FROM ' . self::t( 'quotas' ) . " WHERE form_id = %d AND option_key = ''", $form_id ) );
	}

	/** Reserva un lugar sin pasarse del cupo, aunque lleguen dos envíos a la vez. */
	public static function quota_reserve( object $form ): bool {
		global $wpdb;
		if ( (int) $form->settings['quota'] <= 0 ) {
			return true;
		}
		return 1 === (int) $wpdb->query( $wpdb->prepare(
			'UPDATE ' . self::t( 'quotas' ) . " SET used = used + 1 WHERE form_id = %d AND option_key = '' AND used < capacity",
			$form->id
		) );
	}

	public static function quota_release( object $form ): void {
		global $wpdb;
		if ( (int) $form->settings['quota'] > 0 ) {
			$wpdb->query( $wpdb->prepare( 'UPDATE ' . self::t( 'quotas' ) . " SET used = GREATEST(used, 1) - 1 WHERE form_id = %d AND option_key = ''", $form->id ) );
		}
	}

	/**
	 * Si el formulario recibe envíos ahora.
	 *
	 * @return array{0: string, 1: string} Estado (open | not_yet | closed) y mensaje para el público.
	 */
	public static function availability( object $form ): array {
		$s   = $form->settings;
		$now = current_time( 'Y-m-d H:i' );
		if ( 'published' !== $form->status ) {
			return [ 'closed', $s['closed_text'] ];
		}
		if ( $s['opens_at'] && $now < $s['opens_at'] ) {
			return [ 'not_yet', 'La inscripción abre el ' . self::when( $s['opens_at'] ) . '.' ];
		}
		if ( $s['closes_at'] && $now >= $s['closes_at'] ) {
			return [ 'closed', $s['closed_text'] ];
		}
		if ( (int) $s['quota'] > 0 && self::quota_used( $form->id ) >= (int) $s['quota'] ) {
			return [ 'closed', 'Se completó el cupo. ' . $s['closed_text'] ];
		}
		return [ 'open', '' ];
	}

	/** "20 de noviembre a las 10 horas": las horas en palabras, no "10 h", para los lectores de pantalla. */
	public static function when( string $local ): string {
		$ts = strtotime( $local );
		$h  = (int) gmdate( 'G', $ts );
		$m  = gmdate( 'i', $ts );
		return date_i18n( 'j \d\e F', $ts ) . ' a las ' . ( '00' === $m ? $h : "$h:$m" ) . ' horas';
	}

	public static function can_view( object $form ): bool {
		return current_user_can( CCF_Install::CAP_MANAGE )
			|| ( current_user_can( CCF_Install::CAP_VIEW ) && in_array( get_current_user_id(), array_map( 'intval', $form->settings['responsables'] ), true ) );
	}

	public static function registro( object $form, object $entry ): string {
		return sprintf( '%s-%s-%06d', $form->settings['prefix'], substr( $entry->created_at, 0, 4 ), $entry->id );
	}

	public static function hash( string $value ): string {
		return hash_hmac( 'sha256', mb_strtolower( trim( $value ) ), wp_salt( 'auth' ) );
	}

	public static function insert_entry( object $form, int $version_id, array $values ): object {
		global $wpdb;
		$s     = $form->schema;
		$entry = (object) [
			'form_id'    => $form->id,
			'version_id' => $version_id,
			'status'     => 'pendiente',
			'data'       => wp_json_encode( $values, JSON_UNESCAPED_UNICODE ),
			'email_hash' => ! empty( $s['email_field'] ) && isset( $values[ $s['email_field'] ] ) ? self::hash( $values[ $s['email_field'] ] ) : '',
			'dni_hash'   => ! empty( $s['dni_field'] ) && isset( $values[ $s['dni_field'] ] ) ? self::hash( (string) $values[ $s['dni_field'] ] ) : '',
			'created_at' => current_time( 'mysql' ),
		];
		$wpdb->insert( self::t( 'entries' ), (array) $entry );
		$entry->id = (int) $wpdb->insert_id;
		return $entry;
	}

	public static function is_duplicate( object $form, array $values ): bool {
		global $wpdb;
		$by    = $form->settings['dedupe'];
		$field = $form->schema[ $by . '_field' ] ?? '';
		if ( ! $by || ! $field || ! isset( $values[ $field ] ) ) {
			return false;
		}
		// shortcut: dos envíos idénticos en el mismo segundo pueden pasar los dos; un índice único lo cerraría si hiciera falta.
		return (bool) $wpdb->get_var( $wpdb->prepare(
			'SELECT 1 FROM ' . self::t( 'entries' ) . " WHERE form_id = %d AND {$by}_hash = %s LIMIT 1",
			$form->id, self::hash( (string) $values[ $field ] )
		) );
	}

	public static function entry( int $id ): ?object {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::t( 'entries' ) . ' WHERE id = %d', $id ) );
		if ( $row ) {
			$row->id     = (int) $row->id;
			$row->values = (array) json_decode( $row->data, true );
		}
		return $row;
	}

	/** @return array{0: object[], 1: int} Respuestas de la página pedida y total. */
	public static function entries( int $form_id, array $q = [], int $page = 1, int $per_page = 50 ): array {
		global $wpdb;
		$where = $wpdb->prepare( 'form_id = %d', $form_id );
		if ( ! empty( $q['s'] ) ) {
			$where .= $wpdb->prepare( ' AND data LIKE %s', '%' . $wpdb->esc_like( $q['s'] ) . '%' );
		}
		if ( ! empty( $q['from'] ) ) {
			$where .= $wpdb->prepare( ' AND created_at >= %s', $q['from'] . ' 00:00:00' );
		}
		if ( ! empty( $q['to'] ) ) {
			$where .= $wpdb->prepare( ' AND created_at <= %s', $q['to'] . ' 23:59:59' );
		}
		$t     = self::t( 'entries' );
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $t WHERE $where" );
		$limit = $per_page > 0 ? $wpdb->prepare( ' LIMIT %d OFFSET %d', $per_page, ( max( 1, $page ) - 1 ) * $per_page ) : '';
		$rows  = $wpdb->get_results( "SELECT * FROM $t WHERE $where ORDER BY id DESC$limit" );
		foreach ( $rows as $r ) {
			$r->id     = (int) $r->id;
			$r->values = (array) json_decode( $r->data, true );
		}
		return [ $rows, $total ];
	}

	public static function count( int $form_id ): int {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . self::t( 'entries' ) . ' WHERE form_id = %d', $form_id ) );
	}

	public static function delete_entry( object $form, object $entry ): void {
		global $wpdb;
		$wpdb->delete( self::t( 'entries' ), [ 'id' => $entry->id ] );
		self::quota_release( $form );
		CCF_Install::audit( 'borrar_respuesta', $form->id, $entry->id, self::registro( $form, $entry ) );
	}

	public static function log_mail( int $form_id, int $entry_id, string $kind, string $to, bool $ok, string $error = '' ): void {
		global $wpdb;
		$wpdb->insert( self::t( 'mail_log' ), [
			'form_id'    => $form_id,
			'entry_id'   => $entry_id,
			'kind'       => $kind,
			'recipient'  => $to,
			'status'     => $ok ? 'enviado' : 'fallido',
			'error'      => $error,
			'created_at' => current_time( 'mysql' ),
		] );
	}
}
