<?php
/**
 * Customizer Settings — iCapital Wyoming LLC
 * @package icapital-wyoming
 */
defined( 'ABSPATH' ) || exit;

add_action( 'customize_register', 'icapital_customizer_register' );
function icapital_customizer_register( WP_Customize_Manager $wp_customize ) {

	/* ── Panel: iCapital Settings ── */
	$wp_customize->add_panel( 'icapital_panel', [
		'title'    => __( 'iCapital Wyoming LLC', 'icapital-wyoming' ),
		'priority' => 30,
	] );

	/* ── Section: Hero ── */
	$wp_customize->add_section( 'icapital_hero', [
		'title' => __( 'Hero Section', 'icapital-wyoming' ),
		'panel' => 'icapital_panel',
	] );

	$hero_settings = [
		'icapital_hero_heading' => [
			'label'   => __( 'Hero Heading', 'icapital-wyoming' ),
			'default' => __( 'The Best State To Register Your Business', 'icapital-wyoming' ),
			'type'    => 'text',
		],
		'icapital_hero_sub' => [
			'label'   => __( 'Hero Subheading', 'icapital-wyoming' ),
			'default' => __( 'Form your Wyoming LLC quickly and securely with iCapital Wyoming LLC.', 'icapital-wyoming' ),
			'type'    => 'textarea',
		],
		'icapital_cta_text' => [
			'label'   => __( 'CTA Button Text', 'icapital-wyoming' ),
			'default' => __( 'Form a Wyoming LLC', 'icapital-wyoming' ),
			'type'    => 'text',
		],
		'icapital_cta_url' => [
			'label'   => __( 'CTA Button URL', 'icapital-wyoming' ),
			'default' => '/start',
			'type'    => 'url',
		],
		'icapital_hero_quote' => [
			'label'   => __( 'Hero Testimonial Quote', 'icapital-wyoming' ),
			'default' => __( 'I have used iCapital Wyoming LLC for several years now and I love the fact that they have live customer service.', 'icapital-wyoming' ),
			'type'    => 'textarea',
		],
	];

	foreach ( $hero_settings as $id => $args ) {
		$wp_customize->add_setting( $id, [ 'default' => $args['default'], 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh' ] );
		$wp_customize->add_control( $id, [ 'label' => $args['label'], 'section' => 'icapital_hero', 'type' => $args['type'] ] );
	}

	/* ── Section: Footer ── */
	$wp_customize->add_section( 'icapital_footer', [
		'title' => __( 'Footer', 'icapital-wyoming' ),
		'panel' => 'icapital_panel',
	] );

	$footer_settings = [
		'icapital_footer_tagline' => [
			'label'   => __( 'Footer Tagline', 'icapital-wyoming' ),
			'default' => __( 'Professional Wyoming LLC formation and registered agent services by iCapital Wyoming LLC.', 'icapital-wyoming' ),
			'type'    => 'textarea',
		],
		'icapital_address_line1' => [
			'label'   => __( 'Address Line 1', 'icapital-wyoming' ),
			'default' => '1309 Coffeen Ave',
			'type'    => 'text',
		],
		'icapital_address_line2' => [
			'label'   => __( 'Address Line 2', 'icapital-wyoming' ),
			'default' => 'Sheridan, WY 82801',
			'type'    => 'text',
		],
	];

	foreach ( $footer_settings as $id => $args ) {
		$wp_customize->add_setting( $id, [ 'default' => $args['default'], 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh' ] );
		$wp_customize->add_control( $id, [ 'label' => $args['label'], 'section' => 'icapital_footer', 'type' => $args['type'] ] );
	}
}
