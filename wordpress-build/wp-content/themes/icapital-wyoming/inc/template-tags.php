<?php
/**
 * Template tags / helper functions
 * @package icapital-wyoming
 */
defined( 'ABSPATH' ) || exit;

/** Output the site logo HTML */
function icapital_logo( string $class = '' ) : void {
	printf(
		'<a href="%s" class="icapital-logo %s" rel="home"><div class="icapital-logo__badge">iC</div><span class="icapital-logo__name">iCapital</span><span class="icapital-logo__sub">&nbsp;Wyoming LLC</span></a>',
		esc_url( home_url( '/' ) ),
		esc_attr( $class )
	);
}

/** Return status badge HTML for LLC status */
function icapital_status_badge( string $status ) : string {
	$map = [
		'APPROVED'        => [ 'label' => __( 'Approved', 'icapital-wyoming' ),        'style' => 'background:#dcfce7;color:#166534;border:1px solid #bbf7d0;' ],
		'PENDING_PAYMENT' => [ 'label' => __( 'Pending Payment', 'icapital-wyoming' ), 'style' => 'background:#fef9c3;color:#854d0e;border:1px solid #fef08a;' ],
		'PROCESSING'      => [ 'label' => __( 'Processing', 'icapital-wyoming' ),      'style' => 'background:#dbeafe;color:#1e40af;border:1px solid #bfdbfe;' ],
		'REJECTED'        => [ 'label' => __( 'Rejected', 'icapital-wyoming' ),        'style' => 'background:#fee2e2;color:#991b1b;border:1px solid #fecaca;' ],
	];
	$entry = $map[ $status ] ?? [ 'label' => esc_html( $status ), 'style' => 'background:#f3f4f6;color:#374151;' ];
	return sprintf(
		'<span style="display:inline-flex;align-items:center;padding:0.2rem 0.65rem;border-radius:9999px;font-size:0.75rem;font-weight:600;%s">%s</span>',
		esc_attr( $entry['style'] ),
		esc_html( $entry['label'] )
	);
}
