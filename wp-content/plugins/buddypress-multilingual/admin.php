<?php
/*
 * Admin functions
 */

/**
 * Adds admin menu
 */
function bpml_admin_menu() {
    add_options_page(
            'BuddyPress Multilingual',
            'BuddyPress Multilingual',
            'manage_options',
            'bpml',
            'bpml_admin_page');
}

/**
 * Adds hook for Admin CSS styles and jQuery to admin-side.
 *
 */
function bpml_admin_additional_css_js() {
	wp_enqueue_style('bpml', BPML_PLUGIN_URL . '/style.css', array(), BPML_VERSION);
	wp_enqueue_script('bpml', BPML_PLUGIN_URL . '/scripts.js', array('jquery'), BPML_VERSION);
}

/**
 * Admin notice for required plugins.
 * 
 * @param type $message
 * @param type $class
 */
function bpml_admin_notice_required_plugins() {
    echo '<div class="message updated"><p>'
    . __( 'For BuddyPress Multilingual to work you must enable WPML and BuddyPress.', 'bpml' )
    . '</p></div>';
}


/**
 * Admin notice for WPML settings.
 * 
 * @param type $message
 * @param type $class
 */
function bpml_admin_notice_wpml_settings() {
    echo '<div class="message updated"><p>'
    . __('For BuddyPress Multilingual to work you must set WPML language negotiation to "languages in different directories".', 'bpml')
    . '</p></div>';
}