<?php
/**
 * LLC Applications Custom Post Type
 * @package icapital-web3-core
 */
defined( 'ABSPATH' ) || exit;

class ICapital_CPT {

	public static function register() : void {
		register_post_type( 'icapital_llc', [
			'labels'       => [
				'name'               => __( 'LLC Applications', 'icapital-web3-core' ),
				'singular_name'      => __( 'LLC Application',  'icapital-web3-core' ),
				'add_new_item'       => __( 'Add New Application', 'icapital-web3-core' ),
				'edit_item'          => __( 'Edit LLC Application', 'icapital-web3-core' ),
				'view_item'          => __( 'View LLC Application', 'icapital-web3-core' ),
				'search_items'       => __( 'Search LLC Applications', 'icapital-web3-core' ),
				'not_found'          => __( 'No LLC applications found.', 'icapital-web3-core' ),
				'all_items'          => __( 'All LLC Applications', 'icapital-web3-core' ),
				'menu_name'          => __( 'LLC Applications', 'icapital-web3-core' ),
			],
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => false, // Shown under custom admin menu
			'supports'      => [ 'title', 'custom-fields' ],
			'capability_type' => 'post',
			'capabilities'  => [ 'create_posts' => 'manage_options' ],
			'map_meta_cap'  => true,
			'show_in_rest'  => false, // Access via custom REST endpoints only
		] );

		// Register all meta fields for the CPT
		$meta_fields = [
			'user_id'              => 'integer',
			'company_name'         => 'string',
			'designator'           => 'string',
			'state'                => 'string',
			'industry'             => 'string',
			'registered_agent'     => 'boolean',
			'mail_forwarding'      => 'boolean',
			'ein_application'      => 'boolean',
			'crypto_protection'    => 'boolean',
			'wallet_shipping_addr' => 'string',
			'roi_tracking_status'  => 'string',
			'legal_docs_sent'      => 'boolean',
			'status'               => 'string',
			'state_fee'            => 'number',
		];

		foreach ( $meta_fields as $key => $type ) {
			register_post_meta( 'icapital_llc', $key, [
				'type'              => $type,
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => 'string' === $type ? 'sanitize_text_field' : null,
			] );
		}
	}
}
