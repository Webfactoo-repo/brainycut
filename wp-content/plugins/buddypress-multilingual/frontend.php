<?php
/*
 * Frontend functions
 */
function bpml_get_ID_by_slug($page_slug) {
    $page = get_page_by_path($page_slug);
    if ($page) {
        return $page->ID;
    } else {
        return null;
    }
}
 
/**
 * Test function.
 * 
 * @param <type> $a
 */
function bpml_test($a = '') {
    echo '<pre>'; print_r($a); echo '</pre>';
}

/**
 * Filters site_url() calls.
 *
 * @global  $sitepress
 * @param <type> $url
 * @return <type>
 */
function bpml_site_url_filter($url, $path = '') {
    global $sitepress;
    return rtrim($sitepress->convert_url($url), '/');
}

/**
 * Filters BuddyPress root domain.
 * 
 * @global $sitepress $sitepress
 * @param <type> $url
 * @return <type>
 */
function bpml_bp_core_get_root_domain_filter($url) {
    global $sitepress;
    return rtrim($sitepress->convert_url($url), '/');
}

/**
 * Filters admin URL (removes language).
 * 
 * @global $sitepress $sitepress
 * @param <type> $url
 * @param <type> $path
 * @param <type> $blog_id
 * @return <type>
 */
function bpml_admin_url_filter($url, $path, $blog_id) {
    if ( bpml_is_language_per_domain() ) return $url;
    $url = str_replace('/' . ICL_LANGUAGE_CODE . '/wp-admin', '/wp-admin/', $url);
    return $url;
}

/**
 * Filters bp_uri.
 *
 * This URI is important for BuddyPress.
 * By that it determines some components and actions.
 * We remove language component so BP can determine things right.
 *
 * @param <type> $url
 * @return <type>
 */
function bpml_bp_uri_filter($url) {
    
    if ( bpml_is_language_per_domain() ) return $url;
    
    global $sitepress, $post;
    $default_language = $sitepress->get_default_language();
	
    if ($default_language == ICL_LANGUAGE_CODE) {
        return $url;
    }

    return preg_replace('/(\/)?' . ICL_LANGUAGE_CODE . '\//', '$1', $url, 1);
}

/**
 * Stores/returns debug messages.
 * 
 * @staticvar array $messages
 * @param <type> $message
 * @param <type> $class
 * @return array
 */
function bpml_debug($message, $class = 'bpml-debug-updated') {
    if (!current_user_can('administrator') || !defined('BPML_DEBUG') || !BPML_DEBUG) {
        return '';
    }
    static $messages = array();
    if ($message == 'get') {
        return $messages;
    }
    $messages[] = array('message' => $message, 'class' => 'updated bpml-debug ' . $class);
}

/**
 * BPML language switcher filter.
 * 
 * Active only on BP pages.
 * 
 * @uses add_filter( 'icl_ls_languages', 'bpml_icl_ls_languages_filter' );
 * @uses bp_current_component()
 *
 * @global <type> $sitepress
 * @param <type> $languages Active languages on current page
 * @return <type>
 */
function bpml_icl_ls_languages_filter( $languages ) {
    
    // only if BP page, translation exists or home(?) 
    if ( !bp_current_component() || count( $languages ) == 1 ) {
        return $languages;
    }

    global $sitepress, $bp;

    // Get current page
    if ( isset( $languages[$sitepress->get_current_language()]['url'] )
            && isset( $bp->canonical_stack['canonical_url'] ) ) {
        $_url = $languages[$sitepress->get_current_language()]['url'];
        // Append everything after base URL (canonical actions, items, components...)
        if ( strpos( $bp->canonical_stack['canonical_url'], $_url ) === 0
                && strlen( $bp->canonical_stack['canonical_url'] ) > strlen( $_url ) ) {
            $_add_url = substr( $bp->canonical_stack['canonical_url'],
                    strlen( $_url ) );
        }
    }

    foreach ( $languages as $code => &$language ) {
        // Filter only other languages (no home or default language)
        if ( $code != $sitepress->get_current_language()
                && $sitepress->language_url( $code ) != $language['url']
                && !empty( $_add_url ) ) {
            $language['url'] = rtrim( $language['url'], '/\\' ) . '/'
                    . trim( $_add_url, '/\\' ) . '/';
        }
    }

    return $languages;
}

/**
 * Removes WPML post availability the_content filter.
 * 
 * @global type $icl_language_switcher
 */
function bpml_remove_wpml_post_availability_hook() {
    if ( bp_current_component() ) {
        global $icl_language_switcher;
        remove_filter( 'the_content',
                array($icl_language_switcher, 'post_availability'), 100 );
    }
}

/**
 * Renders debug info.
 * 
 * @global  $bp
 * @return <type>
 */
function bpml_wp_footer_debug() {

    if (!current_user_can('administrator') || !defined('BPML_DEBUG') || !BPML_DEBUG) {
        return '';
    }
	
    echo '<div id="bpml-debug"><h2>BPML Debug</h2>';
    $messages = bpml_debug('get');
    foreach ($messages as $message) {
        echo bpml_message($message['message'], $message['class']);
    }
    global $bp, $bpml;
	
    echo '<pre>';
    echo '
		<h3>$GLOBALS[\'bpml\']</h3>
		';
    bpml_test( $bpml, false );
    echo '

		<h3>$GLOBALS[\'bp\']</h3> ';
    bpml_test( $bp, false );
    echo '</pre></div>';
	
}