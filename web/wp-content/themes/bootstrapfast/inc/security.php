<?php
/**
 * Inspired by Simon Bradburys cleanup.php fromb4st theme https://github.com/SimonPadbury/b4st
 *
 * @package understrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}
/**
 * Removes the generator tag with WP version numbers. Hackers will use this to find weak and old WP installs
 *
 * @return string
 */
function no_generator() {
	return '';
}

if ( is_singular() ) {
	wp_enqueue_script( 'comment-reply' );
}

add_filter( 'the_generator', 'no_generator' );

/**
 * Show less info to users on failed login for security.
 * (Will not let a valid username be known.)
 *
 * @return string
 */
function show_less_login_info() {
	return '<strong>ERROR</strong>: Stop guessing!';
}

add_filter( 'login_errors', 'show_less_login_info' );
