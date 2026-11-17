<?php
/**
 * POST /wp-json/cc-forms/v1/submit
 *
 * Recibe el envío y lo vuelve a validar entero en el servidor, condicionales incluidas:
 * lo que decide el navegador no cuenta.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CCF_Submit {

	const MIN_SECONDS = 3;   // Menos que esto, lo llenó un robot.
	const MAX_PER_HOUR = 30; // Por IP y formulario: alcanza para una escuela que anota a muchos desde la misma red.

	public static function routes(): void {
		register_rest_route( 'cc-forms/v1', '/submit', [
			'methods'             => 'POST',
			'callback'            => [ self::class, 'handle' ],
			'permission_callback' => '__return_true', // Formulario público; los controles están en handle().
		] );
	}

	/** Ficha firmada con la hora en que se mostró el formulario. */
	public static function token(): string {
		$t = (string) time();
		return $t . '.' . hash_hmac( 'sha256', $t, wp_salt( 'nonce' ) );
	}

	private static function token_ok( string $token ): bool {
		[ $t, $sig ] = array_pad( explode( '.', $token, 2 ), 2, '' );
		$age         = time() - (int) $t;
		return hash_equals( hash_hmac( 'sha256', $t, wp_salt( 'nonce' ) ), $sig ) && $age >= self::MIN_SECONDS && $age < 2 * DAY_IN_SECONDS;
	}

	private static function error( string $message, int $status, array $extra = [] ): WP_REST_Response {
		return new WP_REST_Response( [ 'ok' => false, 'message' => $message ] + $extra, $status );
	}

	public static function handle( WP_REST_Request $req ): WP_REST_Response {
		global $wpdb;
		$p    = (array) $req->get_json_params();
		$form = CCF_Forms::get_by_slug( sanitize_title( (string) ( $p['form'] ?? '' ) ) );
		if ( ! $form ) {
			return self::error( 'No encontramos este formulario.', 404 );
		}

		[ $state, $message ] = CCF_Forms::availability( $form );
		$preview             = 'draft' === $form->status && current_user_can( CCF_Install::CAP_MANAGE );
		if ( 'open' !== $state && ! $preview ) {
			return self::error( $message, 409 );
		}

		if ( '' !== (string) ( $p['hp'] ?? '' ) || ! self::token_ok( (string) ( $p['t'] ?? '' ) ) ) {
			return self::error( 'No pudimos recibir el envío. Recargá la página y probá de nuevo.', 400 );
		}

		$ip  = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '' ) );
		$key = 'ccf_rl_' . md5( $ip . '|' . $form->id );
		$n   = (int) get_transient( $key );
		if ( $n >= self::MAX_PER_HOUR ) {
			return self::error( 'Recibimos demasiados envíos desde esta conexión. Probá de nuevo en una hora.', 429 );
		}
		set_transient( $key, $n + 1, HOUR_IN_SECONDS );

		// Se valida con la versión que la persona tenía en pantalla, si es de este formulario.
		$version = (int) ( $p['version'] ?? 0 );
		$json    = $wpdb->get_var( $wpdb->prepare( 'SELECT schema_json FROM ' . CCF_Install::table( 'form_versions' ) . ' WHERE id = %d AND form_id = %d', $version, $form->id ) );
		if ( ! $json ) {
			return self::error( 'El formulario cambió mientras lo completabas. Recargá la página.', 409 );
		}
		$schema = json_decode( $json, true );
		$data   = (array) ( $p['data'] ?? [] );

		$result = CCF_Schema::evaluate( $schema, $data );
		$errors = $result['errors'];
		if ( empty( $data['_acepto'] ) ) {
			$errors['_acepto'] = 'Para enviar tenés que aceptar el aviso de protección de datos personales.';
		}
		if ( $errors ) {
			return self::error( 'Revisá los campos marcados.', 422, [ 'errors' => $errors, 'steps' => $result['steps'] ] );
		}

		$values         = $result['values'];
		$form->schema   = $schema;
		if ( CCF_Forms::is_duplicate( $form, $values ) ) {
			$field = $schema[ $form->settings['dedupe'] . '_field' ];
			return self::error( 'Ya hay una inscripción con estos datos.', 409, [
				'errors' => [ $field => 'Ya hay una inscripción con este ' . ( 'dni' === $form->settings['dedupe'] ? 'DNI' : 'correo' ) . '. Si necesitás cambiar algo, respondé el correo de confirmación.' ],
				'steps'  => [ $field => CCF_Schema::fields( $schema )[ $field ]['_step'] ],
			] );
		}

		if ( ! CCF_Forms::quota_reserve( $form ) ) {
			return self::error( 'Se completó el cupo justo antes de tu envío. ' . $form->settings['closed_text'], 409 );
		}

		$values['_acepto'] = current_time( 'mysql' ); // Cuándo aceptó; el texto aceptado queda en la versión.
		$entry             = CCF_Forms::insert_entry( $form, $version, $values );
		if ( ! $entry->id ) {
			CCF_Forms::quota_release( $form );
			return self::error( 'No pudimos guardar el envío. Probá de nuevo en unos minutos.', 500 );
		}

		CCF_Mailer::after_submit( $form, $entry, $values );

		return new WP_REST_Response( [
			'ok'       => true,
			'registro' => CCF_Forms::registro( $form, $entry ),
			'message'  => $form->settings['confirm_text'],
		], 201 );
	}
}
