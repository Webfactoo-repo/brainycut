<?php
/*
Plugin Name: WPLMS Front End
Plugin URI: http://www.Vibethemes.com
Description: FRONT END Content Creation plugin for WPLMS 
Version: 1.7.5
Author: VibeThemes
Author URI: http://www.vibethemes.com
License: as Per Themeforest GuideLines
*/
/*
Copyright 2014  VibeThemes  (email : vibethemes@gmail.com)

WPLMS Front End is a plugin made for WPLMS Theme. This plugin is only meant to work with WPLMS and can only be used with WPLMS.
WPLMS Front End program is not a free software; you can not copy, redistribute it and/or modify the code without permission from VibeThemes.
Please consult VibeThemes.com or email us at vibethemes@gmail.com/support@vibethemes.com for more information.
*/

if ( !defined( 'ABSPATH' ) ) exit;



if ( ! class_exists( 'WPLMS_Front_End' ) ) {

    require_once( 'includes/class_wplms_front_end.php' );
    WPLMS_Front_End::instance();
}

if ( ! class_exists( 'WP_Front_End_Editor' ) ) {
    require_once( 'front-end-editor/class-wp-front-end-editor.php' );
    WP_Front_End_Editor::instance();
}



add_action( 'init', 'wplms_front_end_update' );
function wplms_front_end_update() {

	/* Load Plugin Updater */
	require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'autoupdate/class-plugin-update.php' );

	/* Updater Config */
	$config = array(
		'base'      => plugin_basename( __FILE__ ), //required
		'dashboard' => true,
		'repo_uri'  => 'http://www.vibethemes.com/',  //required
		'repo_slug' => 'wplms-front-end',  //required
	);

	/* Load Updater Class */
	new WPLMS_Front_End_Auto_Update( $config );
}

add_action('wp_enqueue_scripts','wplms_front_end_enqueue_scripts');
function wplms_front_end_enqueue_scripts(){
		wp_enqueue_style( 'liveedit-css', plugins_url( 'css/jquery-liveedit.css', __FILE__ ));
        wp_enqueue_style( 'wplms-front-end-css', plugins_url( 'css/wplms_front_end.css' , __FILE__ ));
        wp_enqueue_script( 'liveedit-js', plugins_url( 'js/jquery-liveedit.js', __FILE__ ));
        wp_enqueue_script( 'wplms-front-end-js', plugins_url( 'js/wplms_front_end.js' , __FILE__ ), array( 'jquery-ui-core','jquery-ui-sortable','jquery-ui-slider','jquery-ui-datepicker' ) );
        $translation_array = array(
            'course_title' => __( 'Please change the course title','vibe' ), 
        	'create_course_confrim' => __( 'This will create a new course in the site, do you want to continue ?','vibe' ), 
        	'create_course_confrim_button' => __('Yes, create a new course','wplms-front-end'),
        	'save_course_confrim' => __( 'This will overwrite the previous course settings, do you want to continue ?','vibe' ), 
        	'save_course_confrim_button' => __('Save course','wplms-front-end'),
        	'create_unit_confrim' => __( 'This will create a new unit in the site, do you want to continue ?','vibe' ), 
        	'create_unit_confrim_button' => __('Yes, create a new unit','wplms-front-end'),
        	'save_unit_confrim' => __( 'This will overwrite the existing unit settings, do you want to continue ?','vibe' ), 
        	'saveunit_confrim_button' => __('Yes, save unit settings','wplms-front-end'),
            'create_question_confrim' => __( 'This will create a new question in the site, do you want to continue ?','vibe' ), 
            'create_question_confrim_button' => __('Yes, create a new question','wplms-front-end'),
        	'create_quiz_confrim' => __( 'This will create a new quiz in the site, do you want to continue ?','vibe' ), 
        	'create_quiz_confrim_button' => __('Yes, create a new quiz','wplms-front-end'),
            'save_quiz_confrim' => __( 'This will overwrite the existing quiz settings, do you want to continue ?','vibe' ), 
            'save_quiz_confrim_button' => __('Yes, save quiz settings','wplms-front-end'),
        	'delete_confrim' => __( 'This will delete the unit/quiz from your site, do you want to continue ?','vibe' ), 
        	'delete_confrim_button' => __('Continue','wplms-front-end'),
            'save_confrim' => __( 'This will overwrite the previous settings, do you want to continue ?','vibe' ), 
            'save_confrim_button' => __('Save','wplms-front-end'),
            'create_assignment_confrim' => __( 'This will create a new assignment in the site, do you want to continue ?','vibe' ), 
            'create_assignment_confrim_button' => __('Yes, create a new assignment','wplms-front-end')
        	);
    	wp_localize_script( 'wplms-front-end-js', 'wplms_front_end_messages', $translation_array );
}


add_action( 'plugins_loaded', 'wplms_front_end_language_setup' );
function wplms_front_end_language_setup(){
    $locale = apply_filters("plugin_locale", get_locale(), 'wplms-front-end');
    
    $lang_dir = dirname( __FILE__ ) . '/languages/';
    $mofile        = sprintf( '%1$s-%2$s.mo', 'wplms-front-end', $locale );
    $mofile_local  = $lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

    if ( file_exists( $mofile_global ) ) {
        load_textdomain( 'wplms-front-end', $mofile_global );
    } else {
        load_textdomain( 'wplms-front-end', $mofile_local );
    }   
}

?>