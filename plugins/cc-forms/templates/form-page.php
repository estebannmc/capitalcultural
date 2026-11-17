<?php
/**
 * Página pública del formulario: encabezado y pie del tema, sin migas de pan, barra lateral ni título del tema.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="primary" class="ccf-pagina">
	<?php echo CCF_Front::render( CCF_Front::current() ); // phpcs:ignore WordPress.Security.EscapeOutput -- se escapa en render(). ?>
</main>
<?php
get_footer();
