<?php
/*
 * Admin form functions.
 */

/**
 * Processes admin page submit.
 *
 * @global <type> $wpdb
 */
function bpml_admin_save_settings_submit() {
    if ( current_user_can( 'manage_options' )
            && isset( $_POST['_wpnonce'] )
            && wp_verify_nonce( $_POST['_wpnonce'], 'bpml_save_options' )
            && isset( $_POST['bpml'] ) ) {
		if (isset($_POST['bpml_reset_options'])) {
            bpml_save_settings(bpml_default_settings());
            bpml_store_admin_notice('settings_saved', '<p>Settings set to default</p>');
        } else {
            bpml_admin_save_settings_submit_recursive($_POST['bpml']);
            bpml_save_settings($_POST['bpml']);
            do_action('bpml_settings_saved', $_POST['bpml']);
            bpml_store_admin_notice('settings_saved', '<p>Settings saved</p>');
        }

        wp_redirect(admin_url('options-general.php?page=bpml'));
        exit;
    }
}

/**
 * Sets POST values.
 *
 * @param <type> $array
 */
function bpml_admin_save_settings_submit_recursive($array) {
    foreach ($array as $key => &$value) {
        if (is_array($value)) {
            bpml_admin_save_settings_submit_recursive($value);
        } else if ($value == '0' || $value == '1' || $value == '-1') {
            $value = intval($value);
        }
    }
}

/**
 * Renders admin page.
 *
 * @global <type> $bpml
 */
function bpml_admin_page() {
    $messages = bpml_get_setting( 'admin_notices' );
    if ( !empty( $messages ) ) {
        foreach ( $messages as $message ) {
            echo '<div class="message updated">' . $message . '</div>';
        }
    }
    bpml_delete_setting('admin_notices');
    global $bpml;
    echo '<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
<h2>BuddyPress Multilingual</h2><div class="bpml-admin-form">';
    echo '<form action="" method="post">';
    wp_nonce_field('bpml_save_options');

    echo '<h2>General</h2>';

    echo 'Enable debugging <em>(visible on frontend for admin only)</em><br />';
    echo '<label><input type="radio" name="bpml[debug]" value="1"';
    if ($bpml['debug'])
        echo ' checked="checked"';
    echo '/> Yes</label>&nbsp;&nbsp;';
    echo '<label><input type="radio" name="bpml[debug]" value="0"';
    if (!$bpml['debug'])
        echo ' checked="checked"';
    echo '/> No</label>';

    echo '<br /><br />';

    echo '<input type="submit" value="Save Settings" name="bpml_save_options" class="submit button-primary" />';
    echo '<br /><br />';

    echo '</div>';
}