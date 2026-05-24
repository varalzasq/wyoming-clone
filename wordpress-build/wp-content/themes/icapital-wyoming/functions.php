<?php
/**
 * iCapital Wyoming LLC — functions.php
 *
 * Handles: asset enqueuing, theme support, nav menus, Customizer.
 *
 * @package icapital-wyoming
 */

defined( 'ABSPATH' ) || exit;

define( 'ICAPITAL_THEME_VERSION', '1.0.0' );
define( 'ICAPITAL_THEME_URI', get_template_directory_uri() );

/* ──────────────────────────────────────────
 * 1. Theme Support
 * ────────────────────────────────────────── */
add_action( 'after_setup_theme', 'icapital_setup' );
function icapital_setup() {
	load_theme_textdomain( 'icapital-wyoming', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
	add_theme_support( 'custom-logo', [
		'height'      => 64,
		'width'       => 200,
		'flex-height' => true,
		'flex-width'  => true,
	] );

	register_nav_menus( [
		'primary' => esc_html__( 'Primary Navigation', 'icapital-wyoming' ),
		'footer'  => esc_html__( 'Footer Navigation',  'icapital-wyoming' ),
	] );
}

/* ──────────────────────────────────────────
 * 2. Enqueue Global Assets
 * ────────────────────────────────────────── */
add_action( 'wp_enqueue_scripts', 'icapital_enqueue_scripts' );
function icapital_enqueue_scripts() {
	// Theme CSS
	wp_enqueue_style(
		'icapital-theme',
		ICAPITAL_THEME_URI . '/assets/css/theme.css',
		[],
		ICAPITAL_THEME_VERSION
	);

	// Tailwind (compiled — only if file exists)
	$tailwind = get_template_directory() . '/assets/css/tailwind.css';
	if ( file_exists( $tailwind ) ) {
		wp_enqueue_style(
			'icapital-tailwind',
			ICAPITAL_THEME_URI . '/assets/css/tailwind.css',
			[ 'icapital-theme' ],
			ICAPITAL_THEME_VERSION
		);
	}

	// Theme toggle JS (vanilla, no framework)
	wp_enqueue_script(
		'icapital-theme-toggle',
		ICAPITAL_THEME_URI . '/assets/js/theme-toggle.js',
		[],
		ICAPITAL_THEME_VERSION,
		true
	);

	// Dequeue default WP block styles (not needed for custom theme)
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
}

/* ──────────────────────────────────────────
 * 3. Enqueue React App on App Pages
 * ────────────────────────────────────────── */
add_action( 'wp_enqueue_scripts', 'icapital_enqueue_app_scripts' );
function icapital_enqueue_app_scripts() {
	// Only load on pages using an app template
	$app_templates = [ 'page-dashboard.php', 'page-secure-asset.php', 'page-admin-portal.php' ];
	$current_template = get_page_template_slug();

	if ( ! in_array( $current_template, $app_templates, true ) ) {
		return;
	}

	$app_js = WP_PLUGIN_DIR . '/icapital-web3-core/react-app/dist/icapital-app.js';

	if ( ! file_exists( $app_js ) ) {
		// Fallback: look for dev bundle
		$app_js = WP_PLUGIN_DIR . '/icapital-web3-core/react-app/dist/assets/icapital-app.js';
	}

	wp_enqueue_script(
		'icapital-app',
		plugins_url( 'icapital-web3-core/react-app/dist/icapital-app.js' ),
		[],
		ICAPITAL_THEME_VERSION,
		true // load in footer
	);

	// Pass PHP data to React app
	$current_user = wp_get_current_user();
	wp_localize_script( 'icapital-app', 'icapitalData', [
		'restUrl'     => esc_url_raw( rest_url( 'icapital/v1/' ) ),
		'nonce'       => wp_create_nonce( 'wp_rest' ),
		'isLoggedIn'  => is_user_logged_in(),
		'currentUser' => is_user_logged_in() ? [
			'id'          => $current_user->ID,
			'email'       => $current_user->user_email,
			'displayName' => $current_user->display_name,
			'firstName'   => get_user_meta( $current_user->ID, 'first_name', true ),
			'lastName'    => get_user_meta( $current_user->ID, 'last_name', true ),
		] : null,
		'loginUrl'    => esc_url( wp_login_url( get_permalink() ) ),
		'siteUrl'     => esc_url( home_url() ),
	] );
}

/* ──────────────────────────────────────────
 * 4. Customizer Settings
 * ────────────────────────────────────────── */
require get_template_directory() . '/inc/customizer.php';

/* ──────────────────────────────────────────
 * 5. Template Tags / Helpers
 * ────────────────────────────────────────── */
require get_template_directory() . '/inc/template-tags.php';

/* ──────────────────────────────────────────
 * 6. Disable XML-RPC & Expose Less Info
 * ────────────────────────────────────────── */
add_filter( 'xmlrpc_enabled', '__return_false' );
remove_action( 'wp_head', 'wp_generator' ); // Hide WP version

/* ──────────────────────────────────────────
 * 7. Custom Login Page Redirect
 * ────────────────────────────────────────── */
add_filter( 'login_url', 'icapital_custom_login_url', 10, 3 );
function icapital_custom_login_url( $login_url, $redirect, $force_reauth ) {
	$login_page = get_page_by_path( 'login' );
	if ( $login_page ) {
		$url = get_permalink( $login_page->ID );
		if ( $redirect ) {
			$url = add_query_arg( 'redirect_to', rawurlencode( $redirect ), $url );
		}
		return $url;
	}
	return $login_url;
}

add_action( 'template_redirect', 'icapital_redirect_after_login' );
function icapital_redirect_after_login() {
	if ( is_page( 'login' ) && is_user_logged_in() ) {
		$dashboard = get_page_by_path( 'dashboard' );
		wp_safe_redirect( $dashboard ? get_permalink( $dashboard->ID ) : home_url() );
		exit;
	}
}
