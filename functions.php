<?php

require_once('inc/classes/BB-Controller.class.php');

/*
 * Set the content width based on the theme's design and stylesheet.
**/
if ( ! isset( $content_width ) ) {
	$content_width = 570;
}

/*
 * Removing annoying stuff from the HTML-head
**/

remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

function bb_remove_annoying_stuff() {
	wp_deregister_script('l10n');
}
add_action('init', 'bb_remove_annoying_stuff');

/*
 * Setting the body-class
**/
function bb_body_class() {
	do_action('bb_body_class');
}

/*
 * If it's not a search-engine-crawler visiting the site,
 * the right body-class is set to hide the empty
 * content-element.
**/
function bb_set_body_class() {

	$inst = new Backboned();

	if ( $inst->request_type() != 'standard' ) {
		return;
	}

	echo ' class="request-pending"';

}

add_action('bb_body_class', 'bb_set_body_class', 1);

/*
 * Registering hooks
 *
 * Content-Hook
**/
function bb_content($type) {
	do_action('bb_content', $type);
}

function bb_get_content($type) {

	$inst = new Backboned();
	$inst->content($type);

}

add_action('bb_content', 'bb_get_content', 5);

/*
 * JS-Variables-Hook
**/
function bb_get_js_variables() {

	$inst = new Backboned();

	if ( $inst->request_type() != 'standard' ) {
		return;
	}

	$js_variables = $inst->get('js_variables');

	echo '<script>BB = ' . json_encode($js_variables) . ';</script>';

}

add_action('wp_head', 'bb_get_js_variables', 5);

/*
 * JS-Scripts-Hook
**/
function bb_get_js_scripts() {

	$inst = new Backboned();

	if ( $inst->request_type() != 'standard' ) {
		return;
	}

	$scripts = $inst->get('js_scripts');

	echo $scripts;

}

add_action('wp_footer', 'bb_get_js_scripts', 5);

/*
 * Writing the templates to the DOM
**/
function bb_get_partials() {

	$inst = new Backboned();

	if ( $inst->request_type() != 'standard' ) {
		return;
	}

	$partials = $inst->get('partials');

	echo $partials;

}

add_action('wp_footer', 'bb_get_partials', 5);