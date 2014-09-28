<?php
/*
 * WPML translate.
 */

/**
 * Translates field labels.
 * 
 * Uses icl_t() and icl_register_string().
 */
class BPML_Translate_Profile_Fields
{

    protected $_context = 'Buddypress Multilingual', $_string_prefix = 'profile field ';

    /**
     * Construct fuction.
     */
    public function __construct(){
        add_filter( 'bp_get_the_profile_field_name', array($this, 't_name') );
        add_filter( 'bp_get_the_profile_field_description',
                array($this, 't_description') );
        // Options
        add_filter( 'bp_xprofile_field_get_children', array($this, 't_options') );
        add_filter( 'bp_get_the_profile_field_options_checkbox',
                array($this, 't_checkbox'), 0, 5 );
        add_filter( 'bp_get_the_profile_field_options_radio',
                array($this, 't_radio'), 0, 5 );
        add_filter( 'bp_get_the_profile_field_options_multiselect',
                array($this, 't_multiselect_option'), 0, 5 );
        add_filter( 'bp_get_the_profile_field_options_select',
                array($this, 't_select_option'), 0, 5 );
    }

    /**
     * Translates field name property.
     * 
     * @global type $field
     * @param type $name
     * @return type
     */
    public function t_name( $name ){
        global $field;
        icl_register_string( $this->_context,
                "{$this->_string_prefix}{$field->id} name", $name );
        return icl_t( $this->_context,
                "{$this->_string_prefix}{$field->id} name", $name );
    }

    /**
     * Translates field description property.
     * 
     * @global type $field
     * @param type $description
     * @return type
     */
    public function t_description( $description ){
        global $field;
        icl_register_string( $this->_context,
                "{$this->_string_prefix}{$field->id} description", $description );
        return icl_t( $this->_context,
                "{$this->_string_prefix}{$field->id} description", $description );
    }

    /**
     * Translates options.
     * 
     * @global type $field
     * @param type $children
     * @return type
     */
    public function t_options( $children ){
        global $field;
        foreach ( $children as &$option ) {
            // Just tranlsate description. Name can messup forms.
            if ( !empty( $option->description ) ) {
                icl_register_string( $this->_context,
                        "{$this->_string_prefix}{$field->id} - option {$option->id} description",
                        $option->description );
                $option->description = icl_t( $this->_context,
                        "{$this->_string_prefix}{$field->id} - option {$option->id} description",
                        $option->description );
            }
        }
        return $children;
    }

    /**
     * Translates option name.
     * 
     * @param type $option
     * @param type $field_id
     * @return type
     */
    protected function _t_option_name( $option, $field_id ) {
        if ( !empty( $option->name ) ) {
            icl_register_string( $this->_context,
                    "{$this->_string_prefix}{$field_id} - option {$option->id} name",
                    $option->name );
            return icl_t( $this->_context,
                    "{$this->_string_prefix}{$field_id} - option {$option->id} name",
                    $option->name );
        }
        return isset( $option->name ) ? $option->name : '';
    }

    /**
     * Adjusts HTML output for radio field.
     * 
     * @param type $html
     * @param type $option
     * @param type $field_id
     * @param type $selected
     * @param type $k
     * @return type
     */
    public function t_radio( $html, $option, $field_id, $selected, $k ){
        $label = $this->_t_option_name( $option, $field_id );
        return preg_replace( '/"\>(.*)\<\/label\>/', "\">{$label}</label>",
                $html );
    }

    /**
     * Adjusts HTML output for checkbox field.
     * 
     * @param type $html
     * @param type $option
     * @param type $field_id
     * @param type $selected
     * @param type $k
     * @return type
     */
    public function t_checkbox( $html, $option, $field_id, $selected, $k ){
        return $this->t_radio( $html, $option, $field_id, $selected, $k );
    }

    /**
     * Adjusts HTML output for select field.
     * 
     * @param type $html
     * @param type $option
     * @param type $field_id
     * @param type $selected
     * @param type $k
     * @return type
     */
    public function t_select_option( $html, $option, $field_id, $selected, $k ){
        $label = $this->_t_option_name( $option, $field_id );
        return preg_replace( '/"\>(.*)\<\/option\>/', "\">{$label}</option>",
                $html );
    }

    /**
     * Adjusts HTML output for multiselect field.
     * 
     * @param type $html
     * @param type $option
     * @param type $field_id
     * @param type $selected
     * @param type $k
     * @return type
     */
    public function t_multiselect_option( $html, $option, $field_id, $selected,
            $k ){
        return $this->t_select_option( $html, $option, $field_id, $selected, $k );
    }

}

if ( function_exists( 'icl_t' ) && function_exists( 'icl_register_string' ) ) {
    $GLOBALS['bpml_translate_profile'] = new BPML_Translate_Profile_Fields;
}
