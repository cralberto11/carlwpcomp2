<?php
/**
 * The header.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package BootstrapFast
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">

<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<div class="<?php echo esc_attr( bootstrapfast_container_type() ); ?>">
		<div class="row">
			<header id="masthead" class="container site-header col-xs-12 <?php echo esc_attr( bootstrapfast_main_header_style() ); ?>" role="banner">
				<div class="row">
					<div class="site-branding col-md-6">
						<?php
						if ( bootstrapfast_get_the_logo_url() ) {
							?>
							<div id="site-header">
								<?php the_custom_logo(); ?>
							</div>
							<?php
						} else {
							if ( is_front_page() && is_home() ) {
								?>
								<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
								<?php
							} else {
								?>
								<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
								<?php
							}

							$description = get_bloginfo( 'description', 'display' );
							if ( $description || is_customize_preview() ) {
								?>
								<p class="site-description"><?php echo esc_attr( $description ); ?></p>
								<?php
							}
						}
						?>
					</div><!-- .site-branding -->
					<?php
					if ( is_active_sidebar( 'top-sidebar-1' ) ) {
						dynamic_sidebar( 'top-sidebar-1' );
					}
					?>
				</div>

				<nav id="site-navigation" class="main-navigation" role="navigation">
					<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><?php esc_html_e( 'Primary Menu', 'bootstrapfast' ); ?></button>
					<?php
					wp_nav_menu( array(
						'theme_location' => 'menu-1',
						'menu_id'        => 'primary-menu',
					));
					?>
				</nav><!-- #site-navigation -->
			</header><!-- #masthead -->

			<div id="content" class="container site-content col-xs-12 <?php echo esc_attr( bootstrapfast_main_body_style() ); ?>">
