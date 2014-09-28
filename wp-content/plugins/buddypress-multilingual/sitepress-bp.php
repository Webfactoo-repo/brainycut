<?php
/*
  Plugin Name: BuddyPress Multilingual
  Plugin URI: http://wpml.org/?page_id=2890
  Description: BuddyPress Multilingual. <a href="http://wpml.org/?page_id=2890">Documentation</a>.
  Author: OnTheGoSystems
  Author URI: http://www.onthegosystems.com
  Version: 1.5
 */

/*
  BuddyPress Multilingual is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  BuddyPress Multilingual is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with BuddyPress Multilingual.  If not, see <http://www.gnu.org/licenses/>.
 */

/*
 * BuddyPress Multilingual (BPML)
 * 
 * Plugin mostly just filters BP properties, especially URLs to make BP routing
 * work fine between langauges.
 * 
 * version 1.5
 * - BP URL filtering @see bpml_apply_filters
 * --- BP frontend AJAX URL adds queryies 'lang' and '_bpml_ac'  @see bpml_ajax_frontend_call
 * ----- _bpml_ac - action wp_nonce
 * - BP components URI filtering (helps BP determine right components on translated pages)
 * --- 'bp_uri' filter bpml_bp_uri_filter
 */

/**
 * Define constants
 */
define('BPML_VERSION', '1.5');
define('BPML_PLUGIN_URL', plugins_url(basename(dirname(__FILE__))));

add_action('plugins_loaded', 'bpml_init', 11);


/**
 * Main filtering function used on frontend.
 * 
 * @param type $query_string
 * @param type $object
 * @return type
 */
function bpml_apply_filters() {
    static $applied = false;
    if ( $applied ) return;
    require_once dirname( __FILE__ ) . '/frontend.php';
    // Filter BP AJAX URL (append parameters 'lang' and '_bpml_ac')
    add_filter( 'bp_core_ajax_url', 'bpml_core_ajax_url_filter' );
    // Filter language switcher TODO Deprecated?
    add_filter( 'icl_ls_languages', 'bpml_icl_ls_languages_filter' );
    // Adjust BP pages IDs
    add_filter( 'bp_core_get_directory_page_ids', 'bpml_filter_page_ids', 0 );
    // Remove language TODO Deprecated?
    add_filter( 'admin_url', 'bpml_admin_url_filter', 0, 3 );
    // Convert URL - add language
    add_filter( 'bp_core_get_root_domain',
            'bpml_bp_core_get_root_domain_filter', 0 );
    // Convert URL - add language
    add_filter( 'bp_uri', 'bpml_bp_uri_filter', 0 );
    // Frontend message
    add_action( 'bp_core_render_message', 'bpml_show_frontend_notices' );

    // Rewrite rules
    add_action( 'init', 'bpml_use_verbose_rules' );
    add_filter( 'page_rewrite_rules', 'bpml_page_rewrite_rules_filter' );
    add_filter( 'rewrite_rules_array', 'bpml_rewrite_rules_array_filter' );

    // Remove WPML post availability
    add_action('bp_ready', 'bpml_remove_wpml_post_availability_hook');

    do_action( 'bpml_apply_filters' );
    $applied = true;
}

/**
 * Bootstrap.
 * 
 * @global <type> $sitepress_settings
 */
function bpml_init() {
    global $sitepress_settings;
    if ( defined( 'BP_VERSION' ) && defined( 'ICL_SITEPRESS_VERSION' ) ) {
        if ( !isset( $sitepress_settings['language_negotiation_type'] ) || $sitepress_settings['language_negotiation_type'] == '3' ) {
            require_once dirname( __FILE__ ) . '/admin.php';
            add_action( 'admin_notices', 'bpml_admin_notice_wpml_settings' );
        } else {

            // Check if frontend BP AJAX request
            add_action( 'init', 'bpml_ajax_frontend_call', 0 );
            /*
             * Heartbeat WP API - BP latest activity AJAX status update
             * Displayed on activity page, AJAX updated list of activities.
             * Hooks 'heartbeat_received' and 'heartbeat_nopriv_received'
             * cannot be used because filters need to be applied earlier.
             */
            if ( isset($_POST['action']) && $_POST['action'] == 'heartbeat'
                    && isset( $_POST['screen_id'] ) && $_POST['screen_id'] == 'front'
                    && !empty( $_POST['data']['bp_activity_last_recorded'] ) ) {
                bpml_apply_filters();
            }

            // Dismiss notice
            if ( isset( $_GET['bpml_action'] ) && $_GET['bpml_action'] = 'dismiss' ){
                update_option( 'bpml_dismiss_notice', 'yes' );
            }

            global $bpml;
            $bpml = bpml_get_settings();
            define( 'BPML_DEBUG', bpml_get_setting( 'debug', 0 ) );

            if ( !is_admin() ) {
                // Apply main filter
                bpml_apply_filters();
                // Debug output
                add_action( 'wp_footer', 'bpml_wp_footer_debug' );
            } else {
                // Admin functions
                require_once dirname( __FILE__ ) . '/admin.php';
                // Admin menu
                add_action( 'admin_menu', 'bpml_admin_menu' );
                // Saving setings
                if ( isset( $_GET['page'] ) && $_GET['page'] == 'bpml' ) {
                    require_once dirname( __FILE__ ) . '/admin-form.php';
                    add_action( 'admin_init', 'bpml_admin_save_settings_submit' );
                    add_action( 'admin_init', 'bpml_admin_additional_css_js' );
                }
            }

            include_once dirname( __FILE__ ) . '/translate.php';
            include_once dirname( __FILE__ ) . '/email-notifications.php';

        }
    } else if ( is_admin() ) {
        require_once dirname( __FILE__ ) . '/admin.php';
        add_action( 'admin_notices', 'bpml_admin_notice_required_plugins' );
    }
}

/**
 * Turns on verbose rewrite rules.
 * 
 * @see http://wordpress.stackexchange.com/questions/22438/how-to-make-pages-slug-have-priority-over-any-other-taxonomies-like-custom-post
 * @see http://wordpress.stackexchange.com/questions/16902/permalink-rewrite-404-conflict-wordpress-taxonomies-cpt/16929#16929
 * @see http://wordpress.stackexchange.com/questions/17569/using-postname-for-a-custom-post-type
 * @see http://wordpress.stackexchange.com/a/16929/9244
 * 
 * @global type $wp_rewrite
 */
function bpml_use_verbose_rules(){
	global $wp_rewrite;
    $wp_rewrite->use_verbose_page_rules = true;
}

/**
 * Modifies and collects rewrite rules for pages.
 * 
 * @see bpml_rewrite_rules_array_filter
 * @see http://codex.wordpress.org/Plugin_API/Filter_Reference/page_rewrite_rules
 * @param type $page_rewrite_rules
 * @return type
 */
function bpml_page_rewrite_rules_filter( $page_rewrite_rules ){
    $GLOBALS['bpml_page_rewrite_rules'] = $page_rewrite_rules;
    return array();
}

/**
 * Re-orders rewrite rules by pre-pending collected rules.
 * 
 * @see bpml_page_rewrite_rules_filter
 * @see http://codex.wordpress.org/Plugin_API/Filter_Reference/rewrite_rules_array
 * @param type $rewrite_rules
 * @return type
 */
function bpml_rewrite_rules_array_filter( $rewrite_rules ){
    return $GLOBALS['bpml_page_rewrite_rules'] + $rewrite_rules;
}

/**
 * Returns HTML output for admin message.
 *
 * @param <type> $message
 * @param <type> $class
 * @return <type>
 */
function bpml_message($message, $class = 'updated', $ID = NULL) {
    $ID = is_null($ID) ? '' : ' id="' . $ID . '"';
    return '<div class="message ' . $class . '"' . $ID . '>' . $message . '</div>';
}

/**
 * Returns default BPML settings.
 * 
 * @todo Translate profiles setting
 * @return <type>
 */
function bpml_default_settings() {
    return array(
        'debug' => 0
    );
}

/**
 * Returns all settings.
 * 
 * @return <type>
 */
function bpml_get_settings() {
    return apply_filters('bpml_default_settings',
            get_option('bpml', bpml_default_settings()));
}

/**
 * Returns specific setting.
 * 
 * @global <type> $bpml
 * @param <type> $ID
 * @param <type> $default
 * @return <type>
 */
function bpml_get_setting($ID, $default = NULL) {
    global $bpml;
    if (isset($bpml[$ID])) {
        return $bpml[$ID];
    } else if (!is_null($default)) {
        $bpml[$ID] = $default;
        bpml_save_setting($ID, $default);
        return $default;
    }
}

/**
 * Saves all settings.
 *
 * @global array $bpml
 * @param <type> $data
 */
function bpml_save_settings($data) {
    global $bpml;
    $bpml = $data;
    update_option('bpml', $bpml);
}

/**
 * Saves specific setting.
 * 
 * @global array $bpml
 * @param <type> $ID
 * @param <type> $data
 */
function bpml_save_setting($ID, $data) {
    global $bpml;
    $bpml[$ID] = $data;
    update_option('bpml', $bpml);
}

/**
 * Deletes specific setting.
 * 
 * @global  $bpml
 * @param <type> $ID
 */
function bpml_delete_setting($ID) {
    global $bpml;
    if (isset($bpml[$ID])) {
        unset($bpml[$ID]);
        update_option('bpml', $bpml);
    }
}

/**
 * Caches admin notices.
 *
 * Notices are kept until BPML admin page is visited.
 *
 * @global array $bpml
 * @param <type> $ID
 * @param <type> $message
 */
function bpml_store_admin_notice($ID, $message) {
    global $bpml;
    $bpml['admin_notices'][$ID] = $message;
    bpml_save_setting('admin_notices', $bpml['admin_notices']);
}

/**
 * Caches frontend notices.
 *
 * @global array $bpml
 * @param <type> $ID
 * @param <type> $message
 */
function bpml_store_frontend_notice($ID, $message) {
    global $bpml;
    if (!isset($bpml['frontend_notices'])) {
        $bpml['frontend_notices'] = array();
    }
    $bpml['frontend_notices'][$ID] = $message;
    bpml_save_setting('frontend_notices', $bpml['frontend_notices']);
}

/**
 * Displays frontend notices.
 *
 * @global array $bpml
 * @param <type> $ID
 * @param <type> $message
 */
function bpml_show_frontend_notices() {
    global $bpml;
    if (empty($bpml['frontend_notices'])) {
        return '';
    }
    foreach ($bpml['frontend_notices'] as $message) {
        echo bpml_message('<p>' . $message . '</p>', 'bpml-frontend-notice');
    }
    bpml_delete_setting('frontend_notices');
}

/**
 * Filters pages IDs.
 *
 * @global <type> $sitepress
 * @param <type> $page_ids
 * @return <type>
 */
function bpml_filter_page_ids( $page_ids = array() ){

	foreach( $page_ids as $k => &$page_id ){
		$page_id = icl_object_id( $page_id, 'page', true );
	}

	return $page_ids;
}

/**
 * Filters AJAX requests.
 *
 * @global <type> $sitepress
 * @param <type> $url
 * @return <type>
 */
function bpml_core_ajax_url_filter($url){
	global $sitepress;
	
	$url = add_query_arg( array(
        'lang' => $sitepress->get_current_language(),
        '_bpml_ac' => wp_create_nonce( 'filter_frontend' ),
        ), $url );
	
	return $url;
}


/**
 * BP AJAX call from frontend.
 * 
 * AJAX
 * BPML attaches ?lang=[code]&bpml=[wpnonce] to admin ajax url using:
 * add_filter('bp_core_ajax_url', 'bpml_core_ajax_url_filter');
 * 
 * TODO See add_filter( 'bp_ajax_querystring', 'bpml_ajax_querystring', 10, 2 );
 */
function bpml_ajax_frontend_call() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_GET['_bpml_ac'] )
            && wp_verify_nonce( $_GET['_bpml_ac'], 'filter_frontend' ) ) {
        bpml_apply_filters();
    }
}

/**
 * Checks if WPML language per domain settings.
 * 
 * @global type $sitepress_settings
 * @return type
 */
function bpml_is_language_per_domain() {
    global $sitepress_settings;
    return isset( $sitepress_settings['language_negotiation_type'] )
        && $sitepress_settings['language_negotiation_type'] == '2';
}

/**
 * Checks if WPML language per dir settings.
 * 
 * @global type $sitepress_settings
 * @return type
 */
function bpml_is_language_per_dir() {
    global $sitepress_settings;
    return isset( $sitepress_settings['language_negotiation_type'] )
        && $sitepress_settings['language_negotiation_type'] == '1';
}