<?php
/**
 * Tablas propias, roles y migraciones.
 *
 * El prefijo de WordPress en Capital Cultural ya es `cc_`, así que las tablas quedan
 * `cc_ccf_forms`, `cc_ccf_entries`… Siempre se arman con $wpdb->prefix.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CCF_Install {

	const CAP_MANAGE = 'ccf_manage'; // Crear, editar, publicar formularios y ajustes.
	const CAP_VIEW   = 'ccf_view';   // Ver y exportar respuestas (de los formularios asignados).

	public static function table( string $name ): string {
		global $wpdb;
		return $wpdb->prefix . 'ccf_' . $name;
	}

	public static function activate(): void {
		self::create_tables();
		self::add_roles();
		CCF_Front::add_rewrite();
		flush_rewrite_rules();
		add_option( 'ccf_legal_text', self::default_legal_text() );
	}

	public static function maybe_upgrade(): void {
		if ( (int) get_option( 'ccf_db_version' ) < CCF_DB_VERSION ) {
			self::create_tables();
			self::add_roles();
		}
	}

	public static function create_tables(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$c = $wpdb->get_charset_collate();

		dbDelta( 'CREATE TABLE ' . self::table( 'forms' ) . " (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			slug varchar(100) NOT NULL,
			title varchar(200) NOT NULL,
			type varchar(40) NOT NULL DEFAULT '',
			status varchar(20) NOT NULL DEFAULT 'draft',
			version_id bigint(20) unsigned NOT NULL DEFAULT 0,
			settings longtext NOT NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY slug (slug)
		) $c;" );

		dbDelta( 'CREATE TABLE ' . self::table( 'form_versions' ) . " (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			form_id bigint(20) unsigned NOT NULL,
			schema_json longtext NOT NULL,
			created_by bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY form_id (form_id)
		) $c;" );

		dbDelta( 'CREATE TABLE ' . self::table( 'entries' ) . " (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			form_id bigint(20) unsigned NOT NULL,
			version_id bigint(20) unsigned NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'pendiente',
			data longtext NOT NULL,
			email_hash char(64) NOT NULL DEFAULT '',
			dni_hash char(64) NOT NULL DEFAULT '',
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY form_created (form_id,created_at),
			KEY form_email (form_id,email_hash),
			KEY form_dni (form_id,dni_hash)
		) $c;" );

		dbDelta( 'CREATE TABLE ' . self::table( 'quotas' ) . " (
			form_id bigint(20) unsigned NOT NULL,
			option_key varchar(100) NOT NULL DEFAULT '',
			capacity int(10) unsigned NOT NULL DEFAULT 0,
			used int(10) unsigned NOT NULL DEFAULT 0,
			PRIMARY KEY  (form_id,option_key)
		) $c;" );

		dbDelta( 'CREATE TABLE ' . self::table( 'mail_log' ) . " (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			form_id bigint(20) unsigned NOT NULL,
			entry_id bigint(20) unsigned NOT NULL DEFAULT 0,
			kind varchar(30) NOT NULL,
			recipient varchar(255) NOT NULL,
			status varchar(20) NOT NULL,
			error text NOT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY form_id (form_id)
		) $c;" );

		dbDelta( 'CREATE TABLE ' . self::table( 'audit_log' ) . " (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			action varchar(40) NOT NULL,
			form_id bigint(20) unsigned NOT NULL DEFAULT 0,
			entry_id bigint(20) unsigned NOT NULL DEFAULT 0,
			detail text NOT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY form_id (form_id)
		) $c;" );

		update_option( 'ccf_db_version', CCF_DB_VERSION );
	}

	public static function add_roles(): void {
		add_role( 'ccf_admin', 'Administrador de formularios', [ 'read' => true, self::CAP_MANAGE => true, self::CAP_VIEW => true, 'upload_files' => true ] );
		add_role( 'ccf_responsable', 'Responsable de área (formularios)', [ 'read' => true, self::CAP_VIEW => true ] );
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$admin->add_cap( self::CAP_MANAGE );
			$admin->add_cap( self::CAP_VIEW );
		}
	}

	public static function default_legal_text(): string {
		// Texto provisorio: lo tiene que revisar el área legal antes de publicar formularios reales.
		return 'La Secretaría de Cultura de la Municipalidad de Santa Fe guarda estos datos en sus propios servidores, según la Ley 25.326 de Protección de Datos Personales. Los usa solo para la finalidad de este formulario y no los comparte con terceros. Podés pedir que se corrijan o se borren escribiendo a capitalcultural@santafeciudad.gov.ar.';
	}

	public static function audit( string $action, int $form_id = 0, int $entry_id = 0, string $detail = '' ): void {
		global $wpdb;
		$wpdb->insert( self::table( 'audit_log' ), [
			'user_id'    => get_current_user_id(),
			'action'     => $action,
			'form_id'    => $form_id,
			'entry_id'   => $entry_id,
			'detail'     => $detail,
			'created_at' => current_time( 'mysql' ),
		] );
	}
}
