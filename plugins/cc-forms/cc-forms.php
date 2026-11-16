<?php
/**
 * Plugin Name: Capital Cultural – Formularios
 * Description: Formularios propios de Capital Cultural: inscripciones y solicitudes por etapas, guardadas en el servidor de la Municipalidad. Reemplaza a Google Forms.
 * Version: 0.1.0
 * Requires PHP: 8.1
 * Requires at least: 6.4
 * Author: Esteban Maximiliano Córdoba
 * Text Domain: cc-forms
 * License: GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const CCF_VERSION = '0.1.0';
const CCF_DB_VERSION = 1;
define( 'CCF_FILE', __FILE__ );
define( 'CCF_DIR', plugin_dir_path( __FILE__ ) );
define( 'CCF_URL', plugin_dir_url( __FILE__ ) );

require_once CCF_DIR . 'includes/class-ccf-schema.php';
require_once CCF_DIR . 'includes/class-ccf-install.php';
require_once CCF_DIR . 'includes/class-ccf-forms.php';
require_once CCF_DIR . 'includes/class-ccf-mailer.php';
require_once CCF_DIR . 'includes/class-ccf-submit.php';
require_once CCF_DIR . 'includes/class-ccf-front.php';
require_once CCF_DIR . 'includes/class-ccf-admin.php';

register_activation_hook( __FILE__, [ 'CCF_Install', 'activate' ] );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
// Sin uninstall.php a propósito: desinstalar el plugin nunca borra respuestas.

add_action( 'plugins_loaded', [ 'CCF_Install', 'maybe_upgrade' ] );
add_action( 'init', [ 'CCF_Front', 'init' ] );
add_action( 'rest_api_init', [ 'CCF_Submit', 'routes' ] );
if ( is_admin() ) {
	CCF_Admin::init();
}
