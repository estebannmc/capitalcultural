<?php
/**
 * Plantilla HTML de los correos. Versión de docs/formularios/correo/confirmacion.html con datos reales.
 * Reglas de correo: tablas, estilos en línea, sin SVG ni fuentes web, imágenes desde santafeciudad.gov.ar.
 *
 * @var array $m kicker, title, intro, registro, rows, help, button, legal, banner, banner_alt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$cell  = 'padding:10px 0; border-top:1px solid #dadad9;';
$white = 'color:#ffffff; text-decoration:none;';
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light only">
<title><?php echo esc_html( $m['title'] ); ?></title>
<style>
@media (max-width: 620px) {
	.px { padding-left: 20px !important; padding-right: 20px !important; }
	.h1 { font-size: 24px !important; }
	.col { display: block !important; width: 100% !important; padding: 0 0 18px !important; }
}
</style>
</head>
<body style="margin:0; padding:0; background:#f4f4f3;">
<div style="display:none; max-height:0; overflow:hidden;">Número de registro: <?php echo esc_html( $m['registro'] ); ?></div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f4f3;">
<tr><td align="center" style="padding:24px 12px 32px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="table-layout:fixed; max-width:600px; font-family:'Encode Sans', Arial, Helvetica, sans-serif; color:#002751;">

	<tr><td style="padding:4px 4px 20px;">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( CCF_URL . 'assets/email/logo-sf-cultura.png' ); ?>" width="171" height="34" alt="SF Cultura · Capital Cultural" style="display:block; border:0;"></a>
	</td></tr>

	<tr><td style="background:#ffffff; border-radius:8px;">
		<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
			<?php if ( $m['banner'] ) : ?>
				<tr><td><img src="<?php echo esc_url( $m['banner'] ); ?>" width="600" alt="<?php echo esc_attr( $m['banner_alt'] ); ?>" style="display:block; width:100%; max-width:600px; height:auto; border:0; border-radius:8px 8px 0 0;"></td></tr>
			<?php endif; ?>

			<tr><td class="px" style="padding:32px 40px 8px;">
				<p style="margin:0 0 10px; font-size:13px; font-weight:bold; letter-spacing:1px; text-transform:uppercase; color:#606060;"><?php echo esc_html( $m['kicker'] ); ?></p>
				<h1 class="h1" style="margin:0 0 14px; font-size:28px; line-height:1.2; font-weight:bold; color:#002751;"><span style="color:#e63312; font-style:italic;">/</span> <?php echo esc_html( $m['title'] ); ?></h1>
				<p style="margin:0; font-size:16px; line-height:1.6; color:#002751;"><?php echo esc_html( $m['intro'] ); ?></p>
			</td></tr>

			<tr><td class="px" style="padding:24px 40px 8px;">
				<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#eef6fc; border-radius:8px;">
					<tr><td style="padding:16px 20px;">
						<p style="margin:0 0 2px; font-size:13px; color:#3d4a5c;">Número de registro</p>
						<p style="margin:0; font-size:24px; font-weight:bold; letter-spacing:1px; color:#002751;"><?php echo esc_html( $m['registro'] ); ?></p>
					</td></tr>
				</table>
			</td></tr>

			<?php if ( $m['rows'] ) : ?>
				<tr><td class="px" style="padding:24px 40px 4px;">
					<h2 style="margin:0 0 12px; font-size:19px; font-weight:bold; color:#002751;"><span style="color:#e63312; font-style:italic;">/</span> Resumen</h2>
					<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size:15px; line-height:1.45; border-bottom:1px solid #dadad9;">
						<?php foreach ( $m['rows'] as $label => $value ) : ?>
							<tr><td style="<?php echo esc_attr( $cell ); ?> color:#606060; width:42%; padding-right:12px;" valign="top"><?php echo esc_html( $label ); ?></td><td style="<?php echo esc_attr( $cell ); ?> font-weight:bold;" valign="top"><?php echo esc_html( $value ); ?></td></tr>
						<?php endforeach; ?>
					</table>
				</td></tr>
			<?php endif; ?>

			<?php if ( $m['help'] ) : ?>
				<tr><td class="px" style="padding:22px 40px 8px;"><p style="margin:0; font-size:15px; line-height:1.6;"><?php echo esc_html( $m['help'] ); ?></p></td></tr>
			<?php endif; ?>

			<?php if ( $m['button'] ) : ?>
				<tr><td class="px" style="padding:20px 40px 8px;">
					<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
						<td style="background:#002751; border-radius:6px;"><a href="<?php echo esc_url( $m['button'][0] ); ?>" style="display:inline-block; padding:15px 28px; font-size:16px; font-weight:bold; <?php echo esc_attr( $white ); ?>"><?php echo esc_html( $m['button'][1] ); ?></a></td>
					</tr></table>
				</td></tr>
			<?php endif; ?>

			<tr><td style="padding:16px 0 0;"></td></tr>
			<?php if ( $m['legal'] ) : ?>
				<tr><td class="px" style="padding:18px 40px 22px; background:#f7f7f6; border-top:1px solid #ececeb; border-radius:0 0 8px 8px;">
					<p style="margin:0; font-size:13px; line-height:1.55; color:#3d4a5c;"><strong style="color:#002751;">Protección de datos personales.</strong> La Secretaría de Cultura guarda estos datos en los servidores de la Municipalidad de Santa Fe, según la Ley 25.326, y los usa solo para la finalidad de este formulario. Podés pedir que se corrijan o se borren escribiendo a capitalcultural@santafeciudad.gov.ar.</p>
				</td></tr>
			<?php endif; ?>
		</table>
	</td></tr>

	<tr><td style="padding-top:24px;">
		<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#002751; border-radius:8px;">
			<tr><td class="px" style="padding:32px 40px 8px;">
				<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="color:#ffffff; font-size:14px; line-height:1.6;">
					<tr>
						<td class="col" valign="top" style="width:46%; padding-right:16px;">
							<img src="<?php echo esc_url( CCF_URL . 'assets/email/logo-municipalidad.png' ); ?>" width="90" alt="Municipalidad de Santa Fe" style="display:block; border:0; margin:0 0 12px;">
							<p style="margin:0 0 14px; font-size:16px; font-weight:bold;">Secretaría de Cultura</p>
							<p style="margin:0 0 6px; font-size:15px; font-weight:bold;">Contactanos</p>
							<p style="margin:0;">4571609<br>Boulevard Gálvez 1150, Santa Fe, Argentina<br><a href="mailto:capitalcultural@santafeciudad.gov.ar" style="color:#ffffff;">capitalcultural@santafeciudad.gov.ar</a></p>
						</td>
						<td class="col" valign="top" style="width:31%; padding-right:12px;">
							<p style="margin:0 0 6px; font-size:15px; font-weight:bold;">Links de interés</p>
							<p style="margin:0;"><a href="https://agenda.santafeciudad.gov.ar/" style="<?php echo esc_attr( $white ); ?>">Agenda</a><br><a href="https://santafeciudad.gov.ar/secretaria-de-cultura/convocatorias-vigentes/" style="<?php echo esc_attr( $white ); ?>">Convocatorias Vigentes</a><br><a href="https://santafenoticias.gob.ar/" style="<?php echo esc_attr( $white ); ?>">Noticias</a></p>
						</td>
						<td class="col" valign="top" style="width:23%;">
							<p style="margin:0 0 6px; font-size:15px; font-weight:bold;">Nuestras redes</p>
							<p style="margin:0;"><a href="https://www.instagram.com/cultura.santafecapital/" style="<?php echo esc_attr( $white ); ?>">Instagram</a><br><a href="https://www.youtube.com/@SantaFeCapital" style="<?php echo esc_attr( $white ); ?>">YouTube</a></p>
						</td>
					</tr>
				</table>
			</td></tr>
			<tr><td class="px" style="padding:12px 40px 26px;">
				<p style="margin:0; padding-top:16px; border-top:1px solid #33506f; font-size:13px; line-height:1.5; color:#c9d4e3; text-align:center;">© <?php echo esc_html( gmdate( 'Y' ) ); ?> Capital Cultural — Municipalidad de Santa Fe<br>Recibiste este correo por un formulario de Capital Cultural.</p>
			</td></tr>
		</table>
	</td></tr>

</table>
</td></tr>
</table>
</body>
</html>
