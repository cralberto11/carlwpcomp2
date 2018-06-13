<?php
/**
 * This organizes the loading of css and js file assets.
 *
 * @package BootstrapFast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

/**
 * OOP load directory.
 */
function bootstrapfast_asset_dir() {
	return get_template_directory_uri() . '/assets';
}

/**
 * Cache buster.
 */
function bootstrapfast_stylesuffix() {
	$the_theme = wp_get_theme();
	return $the_theme->get( 'Version' );
}

if ( ! function_exists( 'bootstrapfast_asset_head' ) ) {

	/**
	 * Scripts and assets that needed to load in the header.
	 * JQuery, etc.
	 */
	function bootstrapfast_asset_head() {
		wp_enqueue_script( 'jquery' );
	}
}
add_action( 'wp_enqueue_scripts', 'bootstrapfast_asset_head' );

/**
 * Set of scripts and styles being loaded in the body.
 * Hook action in wp_footer when doing pagespeed optimization later
 */
function bootstrapfast_assets() {
		$the_theme = wp_get_theme();

		wp_enqueue_style( 'bootstrapfast-style', asset_path( '/css/themestyle.min.css' ), false, null );

		wp_enqueue_script( 'bootstrapfastjs', asset_path( '/js/themes.min.js' ), array(), bootstrapfast_stylesuffix(), true );

}
add_action( 'wp_enqueue_scripts', 'bootstrapfast_assets' );

/**
 * Registers an editor stylesheet for the theme.
 */
function wpdocs_theme_add_editor_styles() {
	add_editor_style( asset_path( '/css/editor-style.min.css' ) );
}
add_action( 'admin_init', 'wpdocs_theme_add_editor_styles' );


/**
 * Action to insert hook before the body.
 * You still need to insert this hook inside your body.
 */
function bootstrapfast_body_begin() {
	do_action( 'bootstrapfast_body_begin' );
}

/**
 * Updated assetloader.
 *
 * @param filename $filename text Gets the filename.
 *
 * @return filename Returns the filename of a compiled asset is present otherwise it will return the normal filename for cache busting purposes.
 */
function asset_path( $filename ) {
	$dist_path = bootstrapfast_asset_dir();
	$directory = dirname( $filename ) . '/';
	$file      = basename( $filename );
	static $manifest;

	if ( empty( $manifest ) ) {
		$manifest_path = bootstrapfast_asset_dir() . '/assets.json';
		$manifest      = new JsonManifest( $manifest_path );
	}

	if ( array_key_exists( $file, $manifest->get() ) ) {
		return $dist_path . $directory . $manifest->get()[ $file ];
	} else {
		return $dist_path . $directory . $file;
	}
}
