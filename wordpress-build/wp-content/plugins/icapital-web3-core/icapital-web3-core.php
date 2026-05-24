<?php
/**
 * Plugin Name:       iCapital Web3 Core
 * Plugin URI:        https://icapitalwyomingllc.com
 * Description:       Core business logic for iCapital Wyoming LLC — LLC Applications CPT, REST API endpoints, virtual ledger, and Web3 authentication.
 * Version:           1.0.0
 * Author:            iCapital Wyoming LLC
 * Author URI:        https://icapitalwyomingllc.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       icapital-web3-core
 * Domain Path:       /languages
 *
 * @package icapital-web3-core
 */

defined( 'ABSPATH' ) || exit;

// ── Constants ──────────────────────────────────────────────────
define( 'ICAPITAL_PLUGIN_VERSION', '1.0.0' );
define( 'ICAPITAL_PLUGIN_FILE',    __FILE__ );
define( 'ICAPITAL_PLUGIN_DIR',     plugin_dir_path( __FILE__ ) );
define( 'ICAPITAL_PLUGIN_URL',     plugin_dir_url( __FILE__ ) );

// ── Autoload includes ──────────────────────────────────────────
require_once ICAPITAL_PLUGIN_DIR . 'includes/class-activator.php';
require_once ICAPITAL_PLUGIN_DIR . 'includes/class-cpt.php';
require_once ICAPITAL_PLUGIN_DIR . 'includes/class-rest-auth.php';
require_once ICAPITAL_PLUGIN_DIR . 'includes/class-rest-dashboard.php';
require_once ICAPITAL_PLUGIN_DIR . 'includes/class-rest-wallet.php';
require_once ICAPITAL_PLUGIN_DIR . 'includes/class-rest-llc.php';
require_once ICAPITAL_PLUGIN_DIR . 'includes/class-rest-admin.php';
require_once ICAPITAL_PLUGIN_DIR . 'includes/class-siwe.php';
require_once ICAPITAL_PLUGIN_DIR . 'admin/class-admin-menu.php';

// ── Activation / Deactivation hooks ───────────────────────────
register_activation_hook(   __FILE__, [ 'ICapital_Activator', 'activate'   ] );
register_deactivation_hook( __FILE__, [ 'ICapital_Activator', 'deactivate' ] );

// ── Boot REST routes ───────────────────────────────────────────
add_action( 'rest_api_init', function () {
	( new ICapital_REST_Auth()      )->register_routes();
	( new ICapital_REST_Dashboard() )->register_routes();
	( new ICapital_REST_Wallet()    )->register_routes();
	( new ICapital_REST_LLC()       )->register_routes();
	( new ICapital_REST_Admin()     )->register_routes();
	( new ICapital_SIWE()           )->register_routes();
} );

// ── Boot CPT ──────────────────────────────────────────────────
add_action( 'init', [ 'ICapital_CPT', 'register' ] );

// ── Boot Admin Menu ───────────────────────────────────────────
if ( is_admin() ) {
	( new ICapital_Admin_Menu() )->init();
}
