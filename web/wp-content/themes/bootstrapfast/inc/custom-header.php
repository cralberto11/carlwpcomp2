<?php
/**
 * Adds a custom logo.
 *
 * @link https://developer.wordpress.org/themes/functionality/custom-headers/
 *
 * @package BootstrapFast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

/**
 * Set up the WordPress core custom header feature.
 *
 * @uses bootstrapfast_header_style()
 */
function bootstrapfast_custom_header_setup() {
	add_theme_support( 'custom-header', apply_filters( 'bootstrapfast_custom_header_args', array(
		'default-image'      => '',
		'default-text-color' => 'ffffff',
		'width'              => 1000,
		'height'             => 250,
		'flex-height'        => true,
		'flex-wdidth'        => true,
		'wp-head-callback'   => 'bootstrapfast_header_style',
	) ) );
}

if ( ! function_exists( 'bootstrapfast_header_style' ) ) :
	/**
	 * Styles the header image and text displayed on the blog.
	 *
	 * @see bootstrapfast_custom_header_setup().~
	 */
	function bootstrapfast_header_style() {

		$header_text_color = get_header_textcolor();
		if ( get_theme_support( 'custom-header', 'default-text-color' ) === $header_text_color ) {
			return;
		}

		// If we get this far, we have custom styles. Let's do this.
		?>
		<style type="text/css">
		<?php
		if ( ! display_header_text() ) :
		?>
			.site-title,
			.site-description {
				position: absolute;
				clip: rect(1px, 1px, 1px, 1px);
			}
		<?php
			// If the user has set a custom color for the text use that.
			else :
		?>
			.site-title a,
			.site-description {
				color: #<?php echo esc_attr( $header_text_color ); ?>;
			}
		<?php endif; ?>
		</style>
		<?php
	}
endif;

/**
 * Loads inline css from customizer settings.
 */
function bootstrapfast_customizer_css() {
	?>
	<style type="text/css" id="customizerinlinecss">
		header#masthead {
			background-color: <?php	echo esc_attr( get_theme_mod( 'mainheader_background', '#565656' ) ) . ';'; ?>
			<?php
			if ( true === get_theme_mod( 'headersticky' ) ) {
				?>
					position: sticky;
					top: 0;
					z-index: 1000;
				<?php
			}
			?>
		}
		.main-navigation a,
		.textwidget a {
			color: <?php echo esc_attr( get_theme_mod( 'navlink_color', '#fff' ) ); ?>;
		}
	</style>
	<?php
}
add_action( 'wp_head', 'bootstrapfast_customizer_css' );
