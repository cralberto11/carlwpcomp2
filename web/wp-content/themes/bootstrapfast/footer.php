<?php
/**
 * The template for displaying the footer.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package BootstrapFast
 */

?>
			</div><!-- #contennt -->
		</div><!-- .row -->
	</div><!-- .container -->
	<footer id="colophon" class="site-footer <?php echo esc_attr( bootstrapfast_container_type() ); ?>" role="contentinfo">
		<div class="row">
			<?php

			if ( is_active_sidebar( 'footer-sidebar-1' ) ) {
				?>
				<div class="col-md-3">
					<?php dynamic_sidebar( 'footer-sidebar-1' ); ?>
				</div>
				<?php
			}

			if ( is_active_sidebar( 'footer-sidebar-2' ) ) {
				?>
				<div class="col-md-3">
					<?php dynamic_sidebar( 'footer-sidebar-2' ); ?>
				</div>
				<?php
			}

			if ( is_active_sidebar( 'footer-sidebar-3' ) ) {
				?>
				<div class="col-md-3">
					<?php dynamic_sidebar( 'footer-sidebar-3' ); ?>
				</div>
				<?php
			}

			if ( is_active_sidebar( 'footer-sidebar-4' ) ) {
				?>
				<div class="col-md-3">
					<?php dynamic_sidebar( 'footer-sidebar-4' ); ?>
				</div>
				<?php
			}

			?>
		</div>
		<div class="row">
			<div class="col-md-12">
				<a href="<?php echo esc_url( __( 'https://wordpress.org/', 'bootstrapfast' ) ); ?>"><?php printf( esc_html( 'Proudly powered by %s', 'bootstrapfast' ), 'WordPress' ); ?></a>
				<span class="sep"> | </span>
				<?php printf( esc_html( 'Theme: %1$s by %2$s.', 'bootstrapfast' ), 'BootStrapFast', '<a href="https://carl.alber2.com/" rel="designer">Carl Alberto</a>' ); ?>
			</div><!-- .col-12 -->
		</div>
	</footer><!-- #colophon -->
<?php wp_footer(); ?>
</body>
</html>
