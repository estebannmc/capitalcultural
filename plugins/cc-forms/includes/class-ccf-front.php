<?php
/**
 * Página pública: /formularios/<slug>, con el encabezado y el pie del tema.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CCF_Front {

	private static ?object $form = null;

	public static function add_rewrite(): void {
		add_rewrite_rule( '^formularios/([a-z0-9-]+)/?$', 'index.php?ccf_form=$matches[1]', 'top' );
	}

	public static function init(): void {
		self::add_rewrite();
		add_filter( 'query_vars', fn( $v ) => array_merge( $v, [ 'ccf_form' ] ) );
		add_action( 'template_redirect', [ self::class, 'route' ] );
		// Si alguien guardó los enlaces permanentes antes de activar el plugin, la regla falta: se regenera una vez.
		$rules = get_option( 'rewrite_rules' );
		if ( is_array( $rules ) && ! isset( $rules['^formularios/([a-z0-9-]+)/?$'] ) ) {
			flush_rewrite_rules( false );
		}
	}

	public static function route(): void {
		$slug = get_query_var( 'ccf_form' );
		if ( ! $slug ) {
			return;
		}
		$form = CCF_Forms::get_by_slug( sanitize_title( $slug ) );
		if ( ! $form || ! $form->schema || ( 'draft' === $form->status && ! current_user_can( CCF_Install::CAP_MANAGE ) ) ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return;
		}
		self::$form = $form;

		// Sin caché: la vigencia, el cupo y la ficha antispam tienen que ser del momento.
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		do_action( 'litespeed_control_set_nocache', 'formulario de cc-forms' );
		nocache_headers();
		status_header( 200 );
		global $wp_query;
		$wp_query->is_404 = false;

		add_filter( 'template_include', fn() => CCF_DIR . 'templates/form-page.php', 99 );
		add_filter( 'pre_get_document_title', fn() => $form->title . ' – Capital Cultural', 99 );
		add_filter( 'wpseo_frontend_presenters', '__return_empty_array' ); // Yoast no conoce esta página: los metadatos van abajo.
		add_filter( 'body_class', fn( $c ) => array_merge( $c, [ 'ccf-page' ] ) );
		add_filter( 'astra_page_layout', fn() => 'no-sidebar' );
		add_filter( 'astra_get_content_layout', fn() => 'plain-container' );
		add_action( 'wp_head', [ self::class, 'meta' ], 1 );
		add_action( 'wp_enqueue_scripts', [ self::class, 'assets' ] );
	}

	public static function assets(): void {
		// La fecha del archivo como versión: cada cambio cambia la URL y la caché de LiteSpeed no sirve una vieja.
		wp_enqueue_style( 'ccf-form', CCF_URL . 'assets/form.css', [], (string) filemtime( CCF_DIR . 'assets/form.css' ) );
		wp_enqueue_script( 'ccf-form', CCF_URL . 'assets/form.js', [], (string) filemtime( CCF_DIR . 'assets/form.js' ), [ 'in_footer' => true, 'strategy' => 'defer' ] );
	}

	public static function meta(): void {
		$f     = self::$form;
		$desc  = wp_trim_words( $f->settings['description'], 30, '…' );
		$image = $f->settings['header_id'] ? wp_get_attachment_image_url( (int) $f->settings['header_id'], 'full' ) : '';
		$url   = home_url( '/formularios/' . $f->slug . '/' );
		if ( 'draft' === $f->status ) {
			echo "<meta name=\"robots\" content=\"noindex, nofollow\">\n";
		}
		printf( "<meta name=\"description\" content=\"%s\">\n", esc_attr( $desc ) );
		printf( "<link rel=\"canonical\" href=\"%s\">\n", esc_url( $url ) );
		printf( "<meta property=\"og:type\" content=\"website\">\n<meta property=\"og:locale\" content=\"es_AR\">\n<meta property=\"og:site_name\" content=\"Capital Cultural\">\n" );
		printf( "<meta property=\"og:title\" content=\"%s\">\n<meta property=\"og:description\" content=\"%s\">\n<meta property=\"og:url\" content=\"%s\">\n", esc_attr( $f->title ), esc_attr( $desc ), esc_url( $url ) );
		if ( $image ) {
			printf( "<meta property=\"og:image\" content=\"%s\">\n<meta property=\"og:image:alt\" content=\"%s\">\n<meta name=\"twitter:card\" content=\"summary_large_image\">\n", esc_url( $image ), esc_attr( $f->settings['header_alt'] ) );
		}
	}

	public static function current(): ?object {
		return self::$form;
	}

	/** El formulario completo: imagen, título, descripción, etapas y revisión. */
	public static function render( object $form ): string {
		$s = $form->settings;
		[ $state, $message ] = CCF_Forms::availability( $form );
		$preview = 'draft' === $form->status;

		ob_start();
		?>
		<div class="ccf" id="ccf-<?php echo esc_attr( $form->slug ); ?>">
			<?php if ( $preview ) : ?>
				<p class="ccf-borrador">Vista previa: este formulario es un borrador y solo lo ve el equipo. Los envíos de prueba se guardan.</p>
			<?php endif; ?>

			<?php echo self::header_image( $s ); // phpcs:ignore WordPress.Security.EscapeOutput -- armado con funciones de WordPress. ?>

			<h1 class="ccf-titulo"><i class="ccf-barra" aria-hidden="true"></i><?php echo esc_html( $form->title ); ?></h1>
			<?php if ( $s['description'] ) : ?>
				<div class="ccf-descripcion"><?php echo wp_kses_post( wpautop( esc_html( $s['description'] ) ) ); ?></div>
			<?php endif; ?>

			<?php if ( 'open' !== $state && ! $preview ) : ?>
				<div class="ccf-tarjeta ccf-cerrado" role="status"><p><?php echo esc_html( $message ); ?></p></div>
			<?php else : ?>
				<?php echo self::form_markup( $form ); // phpcs:ignore WordPress.Security.EscapeOutput -- cada pieza se escapa adentro. ?>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	private static function header_image( array $s ): string {
		if ( ! $s['header_id'] ) {
			return '';
		}
		$img = wp_get_attachment_image( (int) $s['header_id'], 'full', false, [ 'class' => 'ccf-banner', 'alt' => $s['header_alt'], 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(max-width: 1120px) 100vw, 1120px' ] );
		$mob = $s['header_mobile_id'] ? wp_get_attachment_image_url( (int) $s['header_mobile_id'], 'full' ) : '';
		return '<picture class="ccf-banner-pic">' . ( $mob ? '<source media="(max-width: 600px)" srcset="' . esc_url( $mob ) . '">' : '' ) . $img . '</picture>';
	}

	private static function form_markup( object $form ): string {
		$schema = $form->schema;
		$aviso  = $schema['_aviso'] ?? CCF_Forms::legal_text( $form->settings );
		$client = $schema;
		unset( $client['_aviso'] );

		ob_start();
		?>
		<form class="ccf-tarjeta" novalidate
			data-endpoint="<?php echo esc_url( rest_url( 'cc-forms/v1/submit' ) ); ?>"
			data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"
			data-form="<?php echo esc_attr( $form->slug ); ?>"
			data-version="<?php echo (int) $form->version_id; ?>"
			data-token="<?php echo esc_attr( CCF_Submit::token() ); ?>">

			<div class="ccf-progreso">
				<p class="ccf-paso-txt"><strong class="ccf-paso" aria-live="polite"></strong><span>Los campos obligatorios dicen (obligatorio)</span></p>
				<ol class="ccf-etapas" aria-hidden="true"></ol>
			</div>

			<?php foreach ( $schema['steps'] as $i => $step ) : ?>
				<section class="ccf-etapa" data-step="<?php echo (int) $i; ?>" hidden>
					<h2 class="ccf-etapa-titulo" tabindex="-1"><?php echo esc_html( $step['title'] ); ?></h2>
					<?php if ( ! empty( $step['description'] ) ) : ?>
						<p class="ccf-etapa-desc"><?php echo esc_html( $step['description'] ); ?></p>
					<?php endif; ?>
					<div class="ccf-campos">
						<?php foreach ( $step['fields'] as $f ) { echo self::field( $f ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
				</section>
			<?php endforeach; ?>

			<section class="ccf-etapa ccf-revision" data-step="review" hidden>
				<h2 class="ccf-etapa-titulo" tabindex="-1">Revisá tus respuestas</h2>
				<p class="ccf-etapa-desc">Si algo no está bien, tocá "Cambiar" en esa parte.</p>
				<div class="ccf-resumen"></div>
				<div class="ccf-legal">
					<h3><i class="ccf-barra" aria-hidden="true"></i>Protección de datos personales</h3>
					<p><?php echo esc_html( $aviso ); ?></p>
					<label class="ccf-acepto" for="ccf-acepto-<?php echo esc_attr( $form->slug ); ?>">
						<input type="checkbox" id="ccf-acepto-<?php echo esc_attr( $form->slug ); ?>" name="_acepto" value="1" aria-describedby="ccf-err-_acepto">
						<span>Leí el aviso y acepto que se guarden estos datos. (obligatorio)</span>
					</label>
					<p class="ccf-err" id="ccf-err-_acepto" hidden></p>
				</div>
			</section>

			<div class="ccf-hp" aria-hidden="true"><label>No completar este campo <input type="text" name="ccf_web" tabindex="-1" autocomplete="off"></label></div>
			<p class="ccf-alerta" role="alert" hidden></p>

			<div class="ccf-acciones">
				<button type="button" class="ccf-btn ccf-btn-s" data-prev hidden>Anterior</button>
				<button type="submit" class="ccf-btn ccf-btn-p" data-next>Siguiente</button>
			</div>
		</form>

		<div class="ccf-tarjeta ccf-listo" tabindex="-1" hidden></div>
		<noscript><p class="ccf-tarjeta">Para completar este formulario necesitás tener JavaScript activado en el navegador.</p></noscript>
		<script type="application/json" class="ccf-schema"><?php echo wp_json_encode( $client, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP ); ?></script>
		<?php
		return ob_get_clean();
	}

	private static function field( array $f ): string {
		$id   = $f['id'];
		$type = $f['type'];
		$dom  = 'ccf-' . $id;
		$attr = isset( $f['show_if'] ) ? ' data-show-if="' . esc_attr( wp_json_encode( $f['show_if'] ) ) . '"' : '';

		if ( 'info' === $type ) {
			return '<div class="ccf-info" data-field="' . esc_attr( $id ) . '"' . $attr . '>' . wpautop( esc_html( $f['text'] ?? '' ) ) . '</div>';
		}

		$req      = empty( $f['required'] ) ? '' : ' <span class="ccf-ob">(obligatorio)</span>';
		$help     = empty( $f['help'] ) ? '' : '<p class="ccf-ayuda" id="' . $dom . '-ayuda">' . esc_html( $f['help'] ) . '</p>';
		$describe = trim( ( $help ? $dom . '-ayuda ' : '' ) . $dom . '-err' );
		$err      = '<p class="ccf-err" id="' . $dom . '-err" hidden></p>';
		$out      = '<div class="ccf-campo" data-field="' . esc_attr( $id ) . '" data-type="' . esc_attr( $type ) . '"' . $attr . '>';

		if ( in_array( $type, [ 'radio', 'checkbox' ], true ) ) {
			$short = count( $f['options'] ) <= 3 && max( array_map( fn( $o ) => mb_strlen( $o['label'] ), $f['options'] ) ) <= 24;
			$out  .= '<fieldset class="ccf-grupo" aria-describedby="' . esc_attr( $describe ) . '"><legend class="ccf-lbl">' . esc_html( $f['label'] ) . $req . '</legend>' . $help;
			$out  .= '<div class="ccf-opciones' . ( $short ? ' ccf-fila' : '' ) . '">';
			foreach ( $f['options'] as $o ) {
				$extra = ( empty( $o['note'] ) ? '' : '<span class="ccf-nota">' . esc_html( $o['note'] ) . '</span>' )
					. ( empty( $o['link'] ) ? '' : '<a class="ccf-mapa" href="' . esc_url( $o['link'] ) . '" target="_blank" rel="noopener">Ver en el mapa<span class="screen-reader-text"> (se abre en otra pestaña)</span></a>' );
				$out  .= '<label class="ccf-opc"' . ( isset( $o['show_if'] ) ? ' data-show-if="' . esc_attr( wp_json_encode( $o['show_if'] ) ) . '"' : '' ) . '>'
					. '<input type="' . $type . '" name="' . esc_attr( $id ) . ( 'checkbox' === $type ? '[]' : '' ) . '" value="' . esc_attr( $o['value'] ) . '">'
					. '<span class="ccf-det"><span class="ccf-nom">' . esc_html( $o['label'] ) . '</span>' . $extra . '</span></label>';
			}
			return $out . '</div>' . $err . '</fieldset></div>';
		}

		$out .= '<label class="ccf-lbl" for="' . $dom . '">' . esc_html( $f['label'] ) . $req . '</label>';
		$aria = ' aria-describedby="' . esc_attr( $describe ) . '"';
		$ac   = empty( $f['autocomplete'] ) ? '' : ' autocomplete="' . esc_attr( $f['autocomplete'] ) . '"';

		if ( 'select' === $type ) {
			$out .= '<select class="ccf-inp" id="' . $dom . '" name="' . esc_attr( $id ) . '"' . $aria . $ac . '><option value="">Elegí una opción</option>';
			foreach ( $f['options'] as $o ) {
				$out .= '<option value="' . esc_attr( $o['value'] ) . '">' . esc_html( $o['label'] ) . '</option>';
			}
			$out .= '</select>';
		} elseif ( 'textarea' === $type ) {
			$out .= '<textarea class="ccf-inp" id="' . $dom . '" name="' . esc_attr( $id ) . '" rows="4" maxlength="' . (int) ( $f['maxlength'] ?? 2000 ) . '"' . $aria . '></textarea>';
		} else {
			$html_type = [ 'email' => 'email', 'tel' => 'tel', 'date' => 'date', 'time' => 'time' ][ $type ] ?? 'text';
			$mode      = in_array( $type, [ 'dni', 'number' ], true ) ? ' inputmode="numeric"' : '';
			$ac        = $ac ?: ( [ 'email' => ' autocomplete="email"', 'tel' => ' autocomplete="tel"' ][ $type ] ?? '' );
			$range     = in_array( $type, [ 'date', 'number' ], true )
				? ( isset( $f['min'] ) ? ' min="' . esc_attr( $f['min'] ) . '"' : '' ) . ( isset( $f['max'] ) ? ' max="' . esc_attr( $f['max'] ) . '"' : '' ) : '';
			$out      .= '<input class="ccf-inp" type="' . $html_type . '" id="' . $dom . '" name="' . esc_attr( $id ) . '" maxlength="' . (int) ( $f['maxlength'] ?? 200 ) . '"' . $mode . $ac . $range . $aria . '>';
		}
		return $out . $help . $err . '</div>';
	}
}
