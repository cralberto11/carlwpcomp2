<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package BootstrapFast
 */

if ( ! is_active_sidebar( 'main-sidebar-1' ) ) {
	return;
}
?>

<aside id="secondary" class="widget-area col-md-3" role="complementary">
	<?php dynamic_sidebar( 'main-sidebar-1' ); ?>
</aside><!-- #secondary -->
