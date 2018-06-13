<?php
/**
 * Additional custom theme functions.
 *
 * @package BootstrapFast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

/**
 * Setup custom logo.
 */
function bootstrapfast_theme_prefix_setup() {
	add_theme_support( 'custom-logo', array(
		'height'      => 100,
		'width'       => 400,
		'flex-height' => true,
		'flex-width'  => true,
		'header-text' => array( 'site-title', 'site-description' ),
	) );
}

/**
 * Check the logo if existing.
 */
function bootstrapfast_get_the_logo_url() {
	$custom_logo_id = get_theme_mod( 'custom_logo' );
	$image          = wp_get_attachment_image_src( $custom_logo_id, 'full' );
	if ( $custom_logo_id ) {
		return $image[0];
	} else {
		return null;
	}
}
