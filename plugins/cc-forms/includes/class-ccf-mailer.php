<?php
/**
 * Correos: confirmación a quien completa y aviso al equipo.
 *
 * Salen por wp_mail, que en Capital Cultural pasa por WP Mail SMTP y el servidor de la Municipalidad.
 * El aviso al equipo lleva solo el resumen y el enlace al panel: los datos personales no quedan en las casillas.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CCF_Mailer {

	private static string $last_error = '';

	public static function after_submit( object $form, object $entry, array $values ): void {
		$schema   = $form->schema;
		$fields   = CCF_Schema::fields( $schema );
		$registro = CCF_Forms::registro( $form, $entry );
		$rows_of  = function ( array $ids ) use ( $fields, $values ) {
			$rows = [];
			foreach ( $ids as $id ) {
				if ( isset( $values[ $id ], $fields[ $id ] ) ) {
					$rows[ $fields[ $id ]['label'] ] = CCF_Schema::display( $fields[ $id ], $values[ $id ] );
				}
			}
			return $rows;
		};
		$rows = $rows_of( $schema['summary'] ?? [] );

		$email = ! empty( $schema['email_field'] ) ? ( $values[ $schema['email_field'] ] ?? '' ) : '';
		if ( $email ) {
			self::send( $form, $entry, 'confirmacion', $email, 'Recibimos tu envío · ' . $form->title, [
				'kicker'   => $form->title,
				'title'    => '¡Listo! Recibimos tu envío',
				'intro'    => $form->settings['confirm_text'],
				'registro' => $registro,
				'rows'     => $rows,
				'help'     => $form->settings['reply_to'] ? '¿Algo no está bien o tenés dudas? Respondé este correo y te contesta el equipo.' : '',
				'button'   => $form->settings['summary_link'] ? [ $form->settings['summary_link'], 'Conocé más' ] : null,
				'legal'    => true,
			], $form->settings['reply_to'] );
		}

		if ( 'immediate' === $form->settings['notify_mode'] && $form->settings['notify_to'] ) {
			$panel = admin_url( 'admin.php?page=ccf-entries&form=' . $form->id . '&entry=' . $entry->id );
			self::send( $form, $entry, 'aviso_equipo', $form->settings['notify_to'], 'Nueva respuesta: ' . $form->title . ' (' . $registro . ')', [
				'kicker'   => 'Aviso para el equipo',
				'title'    => 'Llegó una respuesta nueva',
				'intro'    => 'Entró una respuesta en "' . $form->title . '". Los datos completos están en el panel, con tu usuario.',
				'registro' => $registro,
				'rows'     => $rows_of( $schema['notify_fields'] ?? $schema['summary'] ?? [] ),
				'help'     => '',
				'button'   => [ $panel, 'Ver la respuesta en el panel' ],
				'legal'    => false,
			] );
		}
	}

	/** Arma el HTML con la plantilla, su versión en texto plano, envía y registra el resultado. */
	private static function send( object $form, object $entry, string $kind, string $to, string $subject, array $m, string $reply_to = '' ): void {
		$m['banner']     = $form->settings['header_id'] ? wp_get_attachment_image_url( (int) $form->settings['header_id'], 'large' ) : '';
		$m['banner_alt'] = $form->settings['header_alt'];
		ob_start();
		include CCF_DIR . 'templates/email.php';
		$html = ob_get_clean();

		$text = $m['title'] . "\n\n" . $m['intro'] . "\n\nNúmero de registro: " . $m['registro'] . "\n";
		foreach ( $m['rows'] as $label => $value ) {
			$text .= "\n$label: $value";
		}
		$text .= $m['help'] ? "\n\n" . $m['help'] : '';
		$text .= $m['button'] ? "\n\n" . $m['button'][1] . ': ' . $m['button'][0] : '';
		$text .= "\n\n--\nSecretaría de Cultura · Municipalidad de Santa Fe\ncapitalcultural@santafeciudad.gov.ar";

		$alt = function ( $phpmailer ) use ( $text ) {
			$phpmailer->AltBody = $text;
		};
		$fail = function ( WP_Error $e ) {
			self::$last_error = $e->get_error_message();
		};
		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];
		if ( $reply_to && is_email( $reply_to ) ) {
			$headers[] = 'Reply-To: ' . $reply_to;
		}

		self::$last_error = '';
		add_action( 'phpmailer_init', $alt );
		add_action( 'wp_mail_failed', $fail );
		$ok = wp_mail( array_map( 'trim', explode( ',', $to ) ), $subject, $html, $headers );
		remove_action( 'phpmailer_init', $alt );
		remove_action( 'wp_mail_failed', $fail );

		CCF_Forms::log_mail( $form->id, $entry->id, $kind, $to, $ok, self::$last_error );
	}
}
