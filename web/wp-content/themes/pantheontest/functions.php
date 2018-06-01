<?php
/**
 * BootStrapFast child functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package BootstrapFast
 */

/**
 * [my_theme_enqueue_styles description]
 */
function bsft_theme_enqueue_styles() {

	$parent_style = 'bootstrapfast-style';
	wp_enqueue_style( $parent_style, get_template_directory_uri() . '/assets/css/themestyle.min.css' );
	wp_enqueue_style( 'child-style',
		get_stylesheet_directory_uri() . '/assets/css/child-style.css',
		array( $parent_style )
	);

}
add_action( 'wp_enqueue_scripts', 'bsft_theme_enqueue_styles' );

/**
 * Load VIP helper.
 */
require_once get_stylesheet_directory() . '/inc/vip-helper.php';
