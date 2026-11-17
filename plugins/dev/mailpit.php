<?php
/**
 * Solo en el entorno local: todos los correos van a Mailpit (http://localhost:8025) y no salen a internet.
 * El remitente imita lo que en el sitio real fuerza WP Mail SMTP.
 */
add_filter( 'wp_mail_from', fn() => 'webmaster@santafeciudad.gov.ar' );
add_filter( 'wp_mail_from_name', fn() => 'Secretaría de Cultura - Municipalidad de Santa Fe' );
add_action( 'phpmailer_init', function ( $mail ) {
	$mail->isSMTP();
	$mail->Host     = 'mailpit';
	$mail->Port     = 1025;
	$mail->SMTPAuth = false;
} );
