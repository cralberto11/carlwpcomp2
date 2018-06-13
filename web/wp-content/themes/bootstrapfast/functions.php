<?php
/**
 * BootstrapFast functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package BootstrapFast
 */

if ( ! function_exists( 'bootstrapfast_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function bootstrapfast_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on BootstrapFast, use a find and replace
		 * to change 'bootstrapfast' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'bootstrapfast', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
			'menu-1' => esc_html__( 'Primary', 'bootstrapfast' ),
		) );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		// Set up the WordPress core custom background feature.
		add_theme_support( 'custom-background', apply_filters( 'bootstrapfast_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		bootstrapfast_custom_header_setup();
		bootstrapfast_theme_prefix_setup();
	}
	endif;
add_action( 'after_setup_theme', 'bootstrapfast_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function bootstrapfast_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'bootstrapfast_content_width', 640 );
}

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function bootstrapfast_widgets_init() {

	register_sidebar( array(
		'name'          => esc_html__( 'Main Sidebar', 'bootstrapfast' ),
		'id'            => 'main-sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'bootstrapfast' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Top Sidebar', 'bootstrapfast' ),
		'id'            => 'top-sidebar-1',
		'description'   => esc_html__( 'Header widgets here.', 'bootstrapfast' ),
		'before_widget' => '<section id="%1$s" class="col-md-6 widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer Sidebar 1', 'bootstrapfast' ),
		'id'            => 'footer-sidebar-1',
		'description'   => esc_html__( 'Footer location 1', 'bootstrapfast' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer Sidebar 2', 'bootstrapfast' ),
		'id'            => 'footer-sidebar-2',
		'description'   => esc_html__( 'Footer location 2', 'bootstrapfast' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer Sidebar 3', 'bootstrapfast' ),
		'id'            => 'footer-sidebar-3',
		'description'   => esc_html__( 'Footer location 3', 'bootstrapfast' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer Sidebar 4', 'bootstrapfast' ),
		'id'            => 'footer-sidebar-4',
		'description'   => esc_html__( 'Footer location 4', 'bootstrapfast' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'bootstrapfast_widgets_init' );

/**
 * Load scripts and styles.
 */
require get_template_directory() . '/inc/assetloader.php';

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Load functions to secure your WP install.
 */
require get_template_directory() . '/inc/security.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

/**
 * Load Customized theme functions.
 */
require get_template_directory() . '/inc/custom-functions.php';

/**
 * Load Customized theme functions.
 */
require get_template_directory() . '/inc/class-jsonmanifest.php';

/**
 * Load icon functions from svg.
 */
require get_template_directory() . '/inc/icon-functions.php';
