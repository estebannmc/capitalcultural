<?php
/**
 * Panel: formularios, respuestas, exportación y ajustes.
 *
 * Administradores (ccf_manage) crean y editan todo. Responsables de área (ccf_view)
 * ven y exportan solo las respuestas de los formularios que tienen asignados.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CCF_Admin {

	private static array $errors = [];

	const STATUS = [ 'draft' => 'Borrador', 'published' => 'Publicado', 'closed' => 'Cerrado' ];

	public static function init(): void {
		add_action( 'admin_menu', [ self::class, 'menu' ] );
		add_action( 'admin_init', [ self::class, 'handle_post' ] );
		add_action( 'admin_post_ccf_export', [ self::class, 'export' ] );
		add_action( 'admin_enqueue_scripts', [ self::class, 'assets' ] );
	}

	public static function menu(): void {
		add_menu_page( 'Formularios', 'Formularios', CCF_Install::CAP_VIEW, 'ccf-forms', [ self::class, 'page_forms' ], 'dashicons-feedback', 26 );
		add_submenu_page( 'ccf-forms', 'Formularios', 'Todos los formularios', CCF_Install::CAP_VIEW, 'ccf-forms', [ self::class, 'page_forms' ] );
		add_submenu_page( 'ccf-forms', 'Respuestas', 'Respuestas', CCF_Install::CAP_VIEW, 'ccf-entries', [ self::class, 'page_entries' ] );
		add_submenu_page( 'ccf-forms', 'Ajustes de formularios', 'Ajustes', CCF_Install::CAP_MANAGE, 'ccf-settings', [ self::class, 'page_settings' ] );
	}

	public static function assets( string $hook ): void {
		if ( ! str_contains( $hook, 'ccf-' ) ) {
			return;
		}
		wp_enqueue_style( 'ccf-admin', CCF_URL . 'assets/admin.css', [], CCF_VERSION );
		if ( 'edit' === ( $_GET['action'] ?? '' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			wp_enqueue_media();
			wp_enqueue_script( 'ccf-admin', CCF_URL . 'assets/admin.js', [ 'jquery' ], CCF_VERSION, true );
		}
	}

	private static function url( array $args ): string {
		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	private static function form_or_die( int $id ): object {
		$form = CCF_Forms::get( $id );
		if ( ! $form || ! CCF_Forms::can_view( $form ) ) {
			wp_die( 'No tenés permiso para ver este formulario.', 403 );
		}
		return $form;
	}

	/* ---------------------------------------------------------------- acciones */

	public static function handle_post(): void {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['ccf_action'] ) ) {
			return;
		}
		$action = sanitize_key( $_POST['ccf_action'] );
		check_admin_referer( 'ccf_' . $action );

		if ( 'delete_entry' === $action ) {
			$form  = self::form_or_die( (int) $_POST['form'] );
			$entry = CCF_Forms::entry( (int) $_POST['entry'] );
			if ( ! current_user_can( CCF_Install::CAP_MANAGE ) || ! $entry || (int) $entry->form_id !== $form->id ) {
				wp_die( 'No tenés permiso para borrar respuestas.', 403 );
			}
			CCF_Forms::delete_entry( $form, $entry );
			wp_safe_redirect( self::url( [ 'page' => 'ccf-entries', 'form' => $form->id, 'deleted' => 1 ] ) );
			exit;
		}

		if ( ! current_user_can( CCF_Install::CAP_MANAGE ) ) {
			wp_die( 'No tenés permiso para editar formularios.', 403 );
		}

		if ( 'new' === $action ) {
			$id = CCF_Forms::create( sanitize_key( $_POST['type'] ?? '' ) );
			wp_safe_redirect( self::url( [ 'page' => 'ccf-forms', 'action' => 'edit', 'id' => $id ] ) );
			exit;
		}

		if ( 'settings' === $action ) {
			$text = sanitize_textarea_field( wp_unslash( $_POST['legal_text'] ?? '' ) );
			if ( '' === $text ) {
				self::$errors[] = 'El aviso de datos personales no puede quedar vacío: sin él no se puede publicar ningún formulario.';
				return;
			}
			update_option( 'ccf_legal_text', $text );
			// Cada formulario pasa a una versión nueva con el texto nuevo; las respuestas viejas conservan el que aceptaron.
			foreach ( CCF_Forms::all() as $f ) {
				$schema = (array) $f->schema;
				$aviso  = CCF_Forms::legal_text( $f->settings );
				if ( ( $schema['_aviso'] ?? '' ) !== $aviso ) {
					$schema['_aviso'] = $aviso;
					CCF_Forms::new_version( $f->id, $schema );
				}
			}
			CCF_Install::audit( 'editar_aviso_legal' );
			wp_safe_redirect( self::url( [ 'page' => 'ccf-settings', 'saved' => 1 ] ) );
			exit;
		}

		if ( 'save' === $action ) {
			self::save_form();
		}
	}

	private static function save_form(): void {
		$form = CCF_Forms::get( (int) $_POST['id'] );
		if ( ! $form ) {
			wp_die( 'El formulario no existe.' );
		}
		$p    = wp_unslash( $_POST );
		$text = fn( $k ) => sanitize_text_field( $p[ $k ] ?? '' );
		$area = fn( $k ) => sanitize_textarea_field( $p[ $k ] ?? '' );
		$date = fn( $k ) => preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $p[ $k ] ?? '' ) ? str_replace( 'T', ' ', $p[ $k ] ) : '';

		$data = [
			'title'  => $text( 'title' ),
			'slug'   => sanitize_title( $p['slug'] ?? '' ),
			'status' => array_key_exists( $p['status'] ?? '', self::STATUS ) ? $p['status'] : 'draft',
		];
		$settings = [
			'description'      => $area( 'description' ),
			'opens_at'         => $date( 'opens_at' ),
			'closes_at'        => $date( 'closes_at' ),
			'quota'            => max( 0, (int) ( $p['quota'] ?? 0 ) ),
			'notify_mode'      => 'none' === ( $p['notify_mode'] ?? '' ) ? 'none' : 'immediate',
			'notify_to'        => implode( ', ', array_filter( array_map( 'sanitize_email', explode( ',', $p['notify_to'] ?? '' ) ) ) ),
			'reply_to'         => sanitize_email( $p['reply_to'] ?? '' ),
			'confirm_text'     => $area( 'confirm_text' ),
			'closed_text'      => $area( 'closed_text' ),
			'dedupe'           => in_array( $p['dedupe'] ?? '', [ 'email', 'dni' ], true ) ? $p['dedupe'] : '',
			'header_id'        => absint( $p['header_id'] ?? 0 ),
			'header_mobile_id' => absint( $p['header_mobile_id'] ?? 0 ),
			'header_alt'       => $area( 'header_alt' ),
			'purpose'          => $area( 'purpose' ),
			'prefix'           => strtoupper( preg_replace( '/[^A-Za-z]/', '', $p['prefix'] ?? '' ) ) ?: 'CC',
			'responsables'     => array_map( 'absint', (array) ( $p['responsables'] ?? [] ) ),
			'summary_link'     => esc_url_raw( $p['summary_link'] ?? '' ),
		];
		$schema = json_decode( $p['schema'] ?? '', true );

		$e = [];
		if ( '' === $data['title'] ) {
			$e[] = 'Falta el título.';
		}
		if ( '' === $data['slug'] ) {
			$e[] = 'Falta el enlace.';
		} else {
			$other = CCF_Forms::get_by_slug( $data['slug'] );
			if ( $other && $other->id !== $form->id ) {
				$e[] = 'Ya hay otro formulario con el enlace /formularios/' . $data['slug'] . '.';
			}
		}
		if ( ! is_array( $schema ) ) {
			$e[] = 'El esquema no es un JSON válido: ' . json_last_error_msg() . '.';
		} else {
			$e = array_merge( $e, CCF_Schema::check( $schema ) );
		}
		if ( $settings['opens_at'] && $settings['closes_at'] && $settings['closes_at'] <= $settings['opens_at'] ) {
			$e[] = 'El cierre tiene que ser después de la apertura.';
		}
		if ( $settings['header_id'] && '' === $settings['header_alt'] ) {
			$e[] = 'La imagen de encabezado necesita su texto alternativo: lo que dice la imagen, con fechas y lugar.';
		}
		if ( $settings['dedupe'] && is_array( $schema ) && empty( $schema[ $settings['dedupe'] . '_field' ] ) ) {
			$e[] = 'Para evitar duplicados por ' . ( 'dni' === $settings['dedupe'] ? 'DNI' : 'correo' ) . ', el esquema necesita "' . $settings['dedupe'] . '_field".';
		}
		if ( 'published' === $data['status'] && '' === trim( get_option( 'ccf_legal_text', '' ) ) ) {
			$e[] = 'No se puede publicar sin el aviso de datos personales. Cargalo en Formularios → Ajustes.';
		}
		if ( $e ) {
			self::$errors = $e;
			return; // La página se vuelve a mostrar con lo que se escribió.
		}

		CCF_Forms::save( $form, $data, $settings, $schema );
		wp_safe_redirect( self::url( [ 'page' => 'ccf-forms', 'action' => 'edit', 'id' => $form->id, 'saved' => 1 ] ) );
		exit;
	}

	public static function export(): void {
		$form = self::form_or_die( (int) ( $_GET['form'] ?? 0 ) );
		check_admin_referer( 'ccf_export_' . $form->id );
		$q             = self::filters();
		[ $entries ]   = CCF_Forms::entries( $form->id, $q, 1, 0 );
		[ $cols, $defs ] = self::columns( $form, $entries );

		CCF_Install::audit( 'exportar', $form->id, 0, count( $entries ) . ' respuestas' . ( array_filter( $q ) ? ', filtradas' : '' ) );
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $form->slug . '-' . current_time( 'Ymd' ) . '.csv"' );
		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" ); // BOM: Excel abre bien las tildes.
		$sep = ';'; // Excel en Argentina separa las columnas con punto y coma.
		fputcsv( $out, array_merge( [ 'Registro', 'Fecha' ], array_values( $cols ), [ 'Aceptó el aviso' ] ), $sep );
		foreach ( $entries as $entry ) {
			$row = [ CCF_Forms::registro( $form, $entry ), $entry->created_at ];
			foreach ( array_keys( $cols ) as $id ) {
				$def   = $defs[ $entry->version_id ][ $id ] ?? null;
				$row[] = $def ? CCF_Schema::display( $def, $entry->values[ $id ] ?? null ) : '';
			}
			$row[] = $entry->values['_acepto'] ?? '';
			fputcsv( $out, array_map( [ 'CCF_Schema', 'csv_cell' ], array_map( 'strval', $row ) ), $sep );
		}
		fclose( $out );
		exit;
	}

	/**
	 * Columnas de todas las versiones que tienen respuestas: primero las de la versión actual,
	 * después las que ya no están. Así una planilla vieja no pierde datos.
	 *
	 * @return array{0: array<string,string>, 1: array<int,array>} id => etiqueta, y campos por versión.
	 */
	private static function columns( object $form, array $entries ): array {
		$defs = [];
		foreach ( array_unique( array_merge( [ (int) $form->version_id ], array_map( fn( $e ) => (int) $e->version_id, $entries ) ) ) as $v ) {
			$schema     = $v === (int) $form->version_id ? $form->schema : CCF_Forms::schema( $v );
			$defs[ $v ] = $schema ? array_filter( CCF_Schema::fields( $schema ), fn( $f ) => 'info' !== $f['type'] ) : [];
		}
		$cols = [];
		foreach ( $defs as $fields ) {
			foreach ( $fields as $id => $f ) {
				$cols[ $id ] = $cols[ $id ] ?? $f['label'];
			}
		}
		return [ $cols, $defs ];
	}

	private static function filters(): array {
		$d = fn( $k ) => preg_match( '/^\d{4}-\d{2}-\d{2}$/', $_GET[ $k ] ?? '' ) ? $_GET[ $k ] : ''; // phpcs:ignore WordPress.Security
		return [ 's' => sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) ), 'from' => $d( 'from' ), 'to' => $d( 'to' ) ]; // phpcs:ignore WordPress.Security.NonceVerification
	}

	/* ---------------------------------------------------------------- páginas */

	private static function notices(): void {
		foreach ( self::$errors as $msg ) {
			echo '<div class="notice notice-error"><p>' . esc_html( $msg ) . '</p></div>';
		}
		if ( isset( $_GET['saved'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			echo '<div class="notice notice-success is-dismissible"><p>Cambios guardados.</p></div>';
		}
		if ( isset( $_GET['deleted'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			echo '<div class="notice notice-success is-dismissible"><p>Respuesta borrada. Quedó registrado en la auditoría.</p></div>';
		}
	}

	public static function page_forms(): void {
		$action = sanitize_key( $_GET['action'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( 'edit' === $action && current_user_can( CCF_Install::CAP_MANAGE ) ) {
			self::page_edit( CCF_Forms::get( (int) $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		$manage = current_user_can( CCF_Install::CAP_MANAGE );
		$forms  = array_filter( CCF_Forms::all(), [ 'CCF_Forms', 'can_view' ] );
		?>
		<div class="wrap ccf-admin">
			<h1 class="wp-heading-inline">Formularios</h1>
			<?php self::notices(); ?>
			<?php if ( $manage ) : ?>
				<form method="post" class="ccf-nuevo">
					<?php wp_nonce_field( 'ccf_new' ); ?>
					<input type="hidden" name="ccf_action" value="new">
					<label for="ccf-type">Nuevo formulario a partir de</label>
					<select id="ccf-type" name="type">
						<?php foreach ( CCF_Forms::templates() as $type => $tpl ) : ?>
							<option value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $tpl['label'] ?? $tpl['title'] ); ?></option>
						<?php endforeach; ?>
						<option value="">Formulario en blanco</option>
					</select>
					<button class="button button-primary">Crear</button>
				</form>
			<?php endif; ?>

			<?php if ( ! $forms ) : ?>
				<p><?php echo $manage ? 'Todavía no hay formularios.' : 'No tenés formularios asignados. Pedíselo a un administrador.'; ?></p>
			<?php else : ?>
				<table class="widefat striped ccf-tabla">
					<thead><tr><th>Formulario</th><th>Estado</th><th>Respuestas</th><th>Cupo</th><th>Vigencia</th><th>Enlace</th></tr></thead>
					<tbody>
					<?php foreach ( $forms as $f ) :
						[ $state ] = CCF_Forms::availability( $f );
						$link      = home_url( '/formularios/' . $f->slug . '/' );
						$quota     = (int) $f->settings['quota'];
						?>
						<tr>
							<td><strong><?php echo $manage ? '<a href="' . esc_url( self::url( [ 'page' => 'ccf-forms', 'action' => 'edit', 'id' => $f->id ] ) ) . '">' . esc_html( $f->title ) . '</a>' : esc_html( $f->title ); ?></strong></td>
							<td><?php echo esc_html( self::STATUS[ $f->status ] . ( 'published' === $f->status ? ( 'open' === $state ? ' · recibe respuestas' : ( 'not_yet' === $state ? ' · todavía no abre' : ' · cerrado' ) ) : '' ) ); ?></td>
							<td><a href="<?php echo esc_url( self::url( [ 'page' => 'ccf-entries', 'form' => $f->id ] ) ); ?>"><?php echo (int) CCF_Forms::count( $f->id ); ?></a></td>
							<td><?php echo $quota ? (int) CCF_Forms::quota_used( $f->id ) . ' de ' . $quota : 'Sin cupo'; ?></td>
							<td><?php echo esc_html( self::vigencia( $f->settings ) ); ?></td>
							<td><a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener"><?php echo 'draft' === $f->status ? 'Vista previa' : 'Abrir'; ?></a></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}

	private static function vigencia( array $s ): string {
		$from = $s['opens_at'] ? 'Desde el ' . CCF_Forms::when( $s['opens_at'] ) : 'Desde que se publica';
		$to   = $s['closes_at'] ? 'hasta el ' . CCF_Forms::when( $s['closes_at'] ) : 'hasta cerrarlo a mano';
		return "$from, $to.";
	}

	private static function page_edit( ?object $form ): void {
		if ( ! $form ) {
			wp_die( 'El formulario no existe.' );
		}
		// Si hubo errores, se muestra lo que se escribió y no lo guardado.
		$posted = self::$errors ? wp_unslash( $_POST ) : null; // phpcs:ignore WordPress.Security.NonceVerification
		$s      = $posted ? array_merge( $form->settings, $posted ) : $form->settings;
		$val    = fn( $k ) => $posted[ $k ] ?? ( $form->$k ?? '' );
		$local  = fn( $v ) => $v ? str_replace( ' ', 'T', substr( $v, 0, 16 ) ) : '';
		$schema = $posted['schema'] ?? wp_json_encode( array_diff_key( (array) $form->schema, [ '_aviso' => 1 ] ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		$users  = get_users( [ 'capability' => CCF_Install::CAP_VIEW, 'orderby' => 'display_name' ] );
		$link   = home_url( '/formularios/' . $form->slug . '/' );
		$img    = fn( $k ) => (int) $s[ $k ] ? wp_get_attachment_image( (int) $s[ $k ], 'medium', false, [ 'class' => 'ccf-img-prev' ] ) : '';
		?>
		<div class="wrap ccf-admin">
			<h1>Editar formulario</h1>
			<p><a href="<?php echo esc_url( self::url( [ 'page' => 'ccf-forms' ] ) ); ?>">← Todos los formularios</a></p>
			<?php self::notices(); ?>

			<div class="ccf-enlace">
				<span>Enlace público:</span> <code id="ccf-link"><?php echo esc_html( $link ); ?></code>
				<button type="button" class="button" data-copy="#ccf-link">Copiar enlace</button>
				<a class="button" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener"><?php echo 'draft' === $form->status ? 'Vista previa' : 'Ver formulario'; ?></a>
			</div>

			<form method="post" class="ccf-edit">
				<?php wp_nonce_field( 'ccf_save' ); ?>
				<input type="hidden" name="ccf_action" value="save">
				<input type="hidden" name="id" value="<?php echo (int) $form->id; ?>">

				<h2>Datos y publicación</h2>
				<table class="form-table" role="presentation">
					<tr><th><label for="title">Título</label></th><td><input class="large-text" id="title" name="title" value="<?php echo esc_attr( $val( 'title' ) ); ?>" required></td></tr>
					<tr><th><label for="slug">Enlace</label></th><td><code><?php echo esc_html( home_url( '/formularios/' ) ); ?></code><input id="slug" name="slug" value="<?php echo esc_attr( $val( 'slug' ) ); ?>" required><p class="description">Corto, en minúsculas y sin tildes. Si lo cambiás después de compartirlo, el enlace viejo deja de andar.</p></td></tr>
					<tr><th><label for="description">Descripción</label></th><td><textarea class="large-text" rows="4" id="description" name="description"><?php echo esc_textarea( $s['description'] ); ?></textarea></td></tr>
					<tr><th><label for="status">Estado</label></th><td><select id="status" name="status"><?php foreach ( self::STATUS as $k => $label ) : ?><option value="<?php echo esc_attr( $k ); ?>" <?php selected( $val( 'status' ), $k ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select>
						<p class="description">Borrador: solo lo ve el equipo. Publicado: recibe respuestas dentro de la vigencia. Cerrado: el enlace sigue andando y muestra el mensaje de cierre.</p></td></tr>
					<tr><th>Vigencia</th><td>
						<label>Abre <input type="datetime-local" name="opens_at" value="<?php echo esc_attr( $local( $s['opens_at'] ) ); ?>"></label>
						<label>Cierra <input type="datetime-local" name="closes_at" value="<?php echo esc_attr( $local( $s['closes_at'] ) ); ?>"></label>
						<p class="description">Hora de Santa Fe. Sin fecha de apertura, abre al publicar; sin fecha de cierre, queda abierto hasta pasarlo a Cerrado.</p></td></tr>
					<tr><th><label for="quota">Cupo total</label></th><td><input type="number" min="0" id="quota" name="quota" value="<?php echo (int) $s['quota']; ?>" class="small-text"> <span class="description">0 = sin cupo. Al completarse, se cierra solo.</span></td></tr>
					<tr><th><label for="dedupe">Evitar duplicados</label></th><td><select id="dedupe" name="dedupe">
						<option value="" <?php selected( $s['dedupe'], '' ); ?>>No</option>
						<option value="dni" <?php selected( $s['dedupe'], 'dni' ); ?>>Por DNI</option>
						<option value="email" <?php selected( $s['dedupe'], 'email' ); ?>>Por correo</option>
					</select></td></tr>
					<tr><th><label for="prefix">Prefijo del registro</label></th><td><input id="prefix" name="prefix" value="<?php echo esc_attr( $s['prefix'] ); ?>" class="small-text" maxlength="6"> <span class="description">Por ejemplo SM: SM-2026-000123.</span></td></tr>
				</table>

				<h2>Imagen de encabezado</h2>
				<table class="form-table" role="presentation">
					<tr><th>Computadora</th><td><input type="hidden" name="header_id" value="<?php echo (int) $s['header_id']; ?>">
						<div class="ccf-img" data-for="header_id"><?php echo $img( 'header_id' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
						<button type="button" class="button" data-media="header_id" data-ratio="4">Elegir imagen</button> <button type="button" class="button-link" data-clear="header_id">Quitar</button>
						<p class="description">1602 × 402 px (4:1), como las de la carpeta "encabezados" del Drive.</p><p class="ccf-aviso-img" data-warn="header_id" hidden></p></td></tr>
					<tr><th>Celular (opcional)</th><td><input type="hidden" name="header_mobile_id" value="<?php echo (int) $s['header_mobile_id']; ?>">
						<div class="ccf-img" data-for="header_mobile_id"><?php echo $img( 'header_mobile_id' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
						<button type="button" class="button" data-media="header_mobile_id" data-ratio="2">Elegir imagen</button> <button type="button" class="button-link" data-clear="header_mobile_id">Quitar</button>
						<p class="description">1080 × 540 px (2:1), con los textos más grandes para que se lean en el celular.</p><p class="ccf-aviso-img" data-warn="header_mobile_id" hidden></p></td></tr>
					<tr><th><label for="header_alt">Texto alternativo</label></th><td><textarea class="large-text" rows="2" id="header_alt" name="header_alt"><?php echo esc_textarea( $s['header_alt'] ); ?></textarea>
						<p class="description">Obligatorio si hay imagen. Repetí lo que dice la imagen: nombre, fechas y lugar.</p></td></tr>
				</table>

				<h2>Después del envío</h2>
				<table class="form-table" role="presentation">
					<tr><th><label for="confirm_text">Mensaje de confirmación</label></th><td><textarea class="large-text" rows="2" id="confirm_text" name="confirm_text"><?php echo esc_textarea( $s['confirm_text'] ); ?></textarea><p class="description">Se muestra en pantalla y va en el correo a quien completa.</p></td></tr>
					<tr><th><label for="closed_text">Mensaje de cerrado</label></th><td><textarea class="large-text" rows="2" id="closed_text" name="closed_text"><?php echo esc_textarea( $s['closed_text'] ); ?></textarea></td></tr>
					<tr><th><label for="reply_to">Responder a</label></th><td><input type="email" class="regular-text" id="reply_to" name="reply_to" value="<?php echo esc_attr( $s['reply_to'] ); ?>"><p class="description">Casilla del área: si la persona responde el correo de confirmación, le llega a esta casilla.</p></td></tr>
					<tr><th><label for="summary_link">Enlace del botón del correo</label></th><td><input type="url" class="large-text" id="summary_link" name="summary_link" value="<?php echo esc_attr( $s['summary_link'] ); ?>"><p class="description">Opcional. Por ejemplo, la página del programa.</p></td></tr>
					<tr><th>Aviso al equipo</th><td>
						<label><input type="radio" name="notify_mode" value="immediate" <?php checked( $s['notify_mode'], 'immediate' ); ?>> Un correo por cada respuesta, al momento</label><br>
						<label><input type="radio" name="notify_mode" value="none" <?php checked( $s['notify_mode'], 'none' ); ?>> Sin aviso: se revisa desde el panel</label>
						<p><label for="notify_to">Casillas que reciben el aviso</label><br><input class="large-text" id="notify_to" name="notify_to" value="<?php echo esc_attr( $s['notify_to'] ); ?>"></p>
						<p class="description">Separadas por coma. El aviso lleva solo el resumen y el enlace al panel, nunca los datos personales completos.</p></td></tr>
				</table>

				<h2>Datos personales y acceso</h2>
				<table class="form-table" role="presentation">
					<tr><th>Aviso (Ley 25.326)</th><td><p class="ccf-legal-base"><?php echo esc_html( get_option( 'ccf_legal_text', '' ) ); ?></p><p class="description">Texto base, fijo en todos los formularios. Lo edita un administrador en Formularios → Ajustes.</p></td></tr>
					<tr><th><label for="purpose">Finalidad específica</label></th><td><textarea class="large-text" rows="2" id="purpose" name="purpose"><?php echo esc_textarea( $s['purpose'] ); ?></textarea><p class="description">Se suma al final del aviso. Por ejemplo: "Los datos se usan para organizar las clases y comunicarnos con la familia."</p></td></tr>
					<tr><th>Responsables</th><td>
						<?php if ( ! $users ) : ?>
							<p class="description">No hay usuarios con acceso a formularios.</p>
						<?php endif; ?>
						<?php foreach ( $users as $u ) : ?>
							<label class="ccf-user"><input type="checkbox" name="responsables[]" value="<?php echo (int) $u->ID; ?>" <?php checked( in_array( $u->ID, array_map( 'intval', (array) $s['responsables'] ), true ) ); ?>> <?php echo esc_html( $u->display_name . ' (' . $u->user_email . ')' ); ?></label>
						<?php endforeach; ?>
						<p class="description">Ven y exportan las respuestas de este formulario, y de ningún otro. Para sumar a alguien, creale un usuario con el rol "Responsable de área (formularios)".</p></td></tr>
				</table>

				<h2>Preguntas</h2>
				<p class="description">Etapas, campos y condiciones, en JSON. Cada cambio crea una versión nueva: las respuestas anteriores quedan con la versión con que se enviaron.</p>
				<textarea name="schema" class="large-text code ccf-schema" rows="28" spellcheck="false"><?php echo esc_textarea( $schema ); ?></textarea>

				<?php submit_button( 'Guardar cambios' ); ?>
			</form>
		</div>
		<?php
	}

	public static function page_entries(): void {
		$form_id = (int) ( $_GET['form'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! $form_id ) {
			echo '<div class="wrap ccf-admin"><h1>Respuestas</h1><p>Elegí un formulario:</p><ul>';
			foreach ( array_filter( CCF_Forms::all(), [ 'CCF_Forms', 'can_view' ] ) as $f ) {
				echo '<li><a href="' . esc_url( self::url( [ 'page' => 'ccf-entries', 'form' => $f->id ] ) ) . '">' . esc_html( $f->title ) . '</a> (' . (int) CCF_Forms::count( $f->id ) . ')</li>';
			}
			echo '</ul></div>';
			return;
		}
		$form = self::form_or_die( $form_id );
		if ( ! empty( $_GET['entry'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			self::page_entry( $form, (int) $_GET['entry'] ); // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		$q                  = self::filters();
		$page               = max( 1, (int) ( $_GET['paged'] ?? 1 ) ); // phpcs:ignore WordPress.Security.NonceVerification
		[ $entries, $total ] = CCF_Forms::entries( $form->id, $q, $page );
		$fields             = CCF_Schema::fields( $form->schema );
		$summary            = array_slice( array_intersect( $form->schema['summary'] ?? [], array_keys( $fields ) ), 0, 4 );
		$export             = wp_nonce_url( add_query_arg( array_filter( $q ) + [ 'action' => 'ccf_export', 'form' => $form->id ], admin_url( 'admin-post.php' ) ), 'ccf_export_' . $form->id );
		$pages              = (int) ceil( $total / 50 );
		?>
		<div class="wrap ccf-admin">
			<h1 class="wp-heading-inline">Respuestas: <?php echo esc_html( $form->title ); ?></h1>
			<a class="page-title-action" href="<?php echo esc_url( $export ); ?>">Exportar CSV<?php echo array_filter( $q ) ? ' (lo filtrado)' : ''; ?></a>
			<?php self::notices(); ?>
			<form method="get" class="ccf-filtros">
				<input type="hidden" name="page" value="ccf-entries"><input type="hidden" name="form" value="<?php echo (int) $form->id; ?>">
				<label>Buscar <input type="search" name="s" value="<?php echo esc_attr( $q['s'] ); ?>"></label>
				<label>Desde <input type="date" name="from" value="<?php echo esc_attr( $q['from'] ); ?>"></label>
				<label>Hasta <input type="date" name="to" value="<?php echo esc_attr( $q['to'] ); ?>"></label>
				<button class="button">Filtrar</button>
				<?php if ( array_filter( $q ) ) : ?><a href="<?php echo esc_url( self::url( [ 'page' => 'ccf-entries', 'form' => $form->id ] ) ); ?>">Ver todas</a><?php endif; ?>
			</form>
			<p><?php echo (int) $total; ?> respuestas<?php echo (int) $form->settings['quota'] ? ' · cupo: ' . (int) CCF_Forms::quota_used( $form->id ) . ' de ' . (int) $form->settings['quota'] : ''; ?>.</p>
			<table class="widefat striped ccf-tabla">
				<thead><tr><th>Registro</th><th>Fecha</th><?php foreach ( $summary as $id ) : ?><th><?php echo esc_html( $fields[ $id ]['label'] ); ?></th><?php endforeach; ?></tr></thead>
				<tbody>
				<?php if ( ! $entries ) : ?>
					<tr><td colspan="<?php echo 2 + count( $summary ); ?>">No hay respuestas<?php echo array_filter( $q ) ? ' con ese filtro' : ' todavía'; ?>.</td></tr>
				<?php endif; ?>
				<?php foreach ( $entries as $e ) : ?>
					<tr>
						<td><a href="<?php echo esc_url( self::url( [ 'page' => 'ccf-entries', 'form' => $form->id, 'entry' => $e->id ] ) ); ?>"><?php echo esc_html( CCF_Forms::registro( $form, $e ) ); ?></a></td>
						<td><?php echo esc_html( mysql2date( 'd/m/Y H:i', $e->created_at ) ); ?></td>
						<?php foreach ( $summary as $id ) : ?><td><?php echo esc_html( CCF_Schema::display( $fields[ $id ], $e->values[ $id ] ?? null ) ); ?></td><?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php if ( $pages > 1 ) : ?>
				<p class="ccf-paginas">
					<?php for ( $i = 1; $i <= $pages; $i++ ) : ?>
						<?php echo $i === $page ? '<strong>' . (int) $i . '</strong>' : '<a href="' . esc_url( add_query_arg( 'paged', $i ) ) . '">' . (int) $i . '</a>'; ?>
					<?php endfor; ?>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	private static function page_entry( object $form, int $entry_id ): void {
		$entry = CCF_Forms::entry( $entry_id );
		if ( ! $entry || (int) $entry->form_id !== $form->id ) {
			wp_die( 'La respuesta no existe.' );
		}
		CCF_Install::audit( 'ver_respuesta', $form->id, $entry->id );
		$schema = CCF_Forms::schema( (int) $entry->version_id ) ?: $form->schema;
		?>
		<div class="wrap ccf-admin">
			<h1>Respuesta <?php echo esc_html( CCF_Forms::registro( $form, $entry ) ); ?></h1>
			<p><a href="<?php echo esc_url( self::url( [ 'page' => 'ccf-entries', 'form' => $form->id ] ) ); ?>">← Respuestas de <?php echo esc_html( $form->title ); ?></a></p>
			<p>Enviada el <?php echo esc_html( mysql2date( 'd/m/Y \a \l\a\s H:i', $entry->created_at ) ); ?>. Aceptó el aviso de datos personales: <?php echo esc_html( $entry->values['_acepto'] ?? 'sin dato' ); ?>.</p>
			<?php foreach ( $schema['steps'] as $step ) :
				$rows = array_filter( $step['fields'], fn( $f ) => isset( $entry->values[ $f['id'] ] ) );
				if ( ! $rows ) {
					continue;
				}
				?>
				<h2><?php echo esc_html( $step['title'] ); ?></h2>
				<table class="widefat striped ccf-ficha"><tbody>
					<?php foreach ( $rows as $f ) : ?>
						<tr><th scope="row"><?php echo esc_html( $f['label'] ); ?></th><td><?php echo nl2br( esc_html( CCF_Schema::display( $f, $entry->values[ $f['id'] ] ) ) ); ?></td></tr>
					<?php endforeach; ?>
				</tbody></table>
			<?php endforeach; ?>

			<?php if ( current_user_can( CCF_Install::CAP_MANAGE ) ) : ?>
				<form method="post" class="ccf-borrar" onsubmit="return confirm('¿Borrar esta respuesta? No se puede deshacer.');">
					<?php wp_nonce_field( 'ccf_delete_entry' ); ?>
					<input type="hidden" name="ccf_action" value="delete_entry">
					<input type="hidden" name="form" value="<?php echo (int) $form->id; ?>">
					<input type="hidden" name="entry" value="<?php echo (int) $entry->id; ?>">
					<button class="button button-link-delete">Borrar esta respuesta</button>
					<span class="description">Para cumplir un pedido de borrado de datos. Libera el lugar del cupo y queda en la auditoría.</span>
				</form>
			<?php endif; ?>
		</div>
		<?php
	}

	public static function page_settings(): void {
		$text = self::$errors ? wp_unslash( $_POST['legal_text'] ?? '' ) : get_option( 'ccf_legal_text', '' ); // phpcs:ignore WordPress.Security.NonceVerification
		?>
		<div class="wrap ccf-admin">
			<h1>Ajustes de formularios</h1>
			<?php self::notices(); ?>
			<form method="post">
				<?php wp_nonce_field( 'ccf_settings' ); ?>
				<input type="hidden" name="ccf_action" value="settings">
				<h2><label for="legal_text">Aviso de protección de datos personales (Ley 25.326)</label></h2>
				<p class="description">Va fijo en todos los formularios, antes del botón Enviar, con su casilla obligatoria. Tiene que decir quién guarda los datos, para qué y cómo pedir que se borren. Al guardarlo, todos los formularios pasan a usar el texto nuevo; cada respuesta conserva el texto que aceptó.</p>
				<textarea id="legal_text" name="legal_text" class="large-text" rows="6"><?php echo esc_textarea( $text ); ?></textarea>
				<?php submit_button( 'Guardar aviso' ); ?>
			</form>
		</div>
		<?php
	}
}
