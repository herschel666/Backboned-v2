<?php

require_once('inc/classes/BB-Controller.class.php');

$bb_controller = null;

/**
 * Give a chance to configure outside of the theme
 * your own theme preferences. They can be about date
 * formats, or anything else.
 **/
if ( ! function_exists( 'bb_after_setup_theme' ) ) {

	/**
	 * Override safely this function to put your own
	 * preferences.
	 *
	 * For example, if you want i18n date format you can
	 * do like this.
	 *
	 * NOTE: make sure you have php5-intl module
	 *       installed on your app server.
	 **/
	function bb_after_setup_theme() {
		update_option('date_format', '%A %e %B %Y');
	}

}

add_action( 'after_setup_theme', 'bb_after_setup_theme');

/**
 * Give a chance to configure outside of the theme
 * your own overload preferences.
 **/
if ( ! function_exists( 'bb_init' ) ) {

	function bb_init() {
		//foo
	}

}

add_action( 'init', 'bb_init', 11);

/**
 * Set the content width based on the theme's design and stylesheet.
 **/
if ( ! isset( $content_width ) ) {
	$content_width = 570;
}

function wp_bb_init( &$WP_Instance ) {
	$GLOBALS['bb_controller'] = new BackbonedController($WP_Instance);
}

add_action( 'wp', 'wp_bb_init', 1 );

/**
 * Registering hooks
 *
 * Content-Hook
 **/
function bb_content($type) {
	do_action('bb_content', $type);
}

function bb_get_content($type) {

	$GLOBALS['bb_controller']->content($type);

}

add_action('bb_content', 'bb_get_content', 5);