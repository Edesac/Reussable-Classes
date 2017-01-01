<?php
/*
 * Plugin Name: Front End Editor
 * Description: 
 * Author: EdesaC
 * Version: 1.0.0
**/

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);


class TMCEFrontEnd {
    protected $_plugin_url;
    protected $_plugin_folder;

    public function __construct($file) {
        if (!$file) {
            throw new Error('Missing 1 argument!');
        }

        $this->_plugin_url = plugin_dir_url($file);
        $this->_plugin_folder = basename(dirname($file));

        add_action('admin_enqueue_scripts', array($this, 'load_admin_scripts_css'));
        add_action('wp_footer', array($this, 'load_website_scripts_css'));

        // add_action( 'admin_head', array($this, 'saveTinymceData'));
        // add_filter( 'mce_buttons', array($this, 'registerTinymceButton'));
        // add_filter( 'mce_external_plugins', array($this, 'registerTinymcePlugin'));

        // add_action('wp_ajax_get_styles', array($this, 'ajaxGetStyles'));
        // add_action('wp_ajax_nopriv_get_styles', array($this, 'ajaxGetStyles'));

        add_action('wp_ajax_save_form', array($this, 'ajaxSaveStyles'));
        add_action('wp_ajax_nopriv_save_form', array($this, 'ajaxSaveStyles'));

        add_shortcode('tmce', array($this, 'showTMCE'));
    }

    /**
     *
     *	@hook add_shorcode
     */
    public function showTMCE($atts = array(), $content = '') {
    	$x = '<form name="entry1">';
    	$x .= '<input type="text" name="firstname">';
    	wp_editor('ftinymce', 'test');
    	$x .= '<input type="submit" value="save">';
    	$x .= '</form>';
    	return $x;
    }

    /**
     * save the customs styles of a post to the database
     */
    public function ajaxSaveStyles() {
        $data = $_POST;
        $ret = $data;

        print_r( json_encode($ret));    
        exit;
    }
    
    public function load_website_scripts_css() {
        echo '<script type="text/javascript">';
        echo 'var $ajax_url = "' . admin_url("admin-ajax.php") . '";';
        echo '</script>';

        wp_enqueue_script($this->_plugin_folder . '-wp-scripts', $this->_plugin_url . '/scripts/wp-scripts.js');
        wp_enqueue_style($this->_plugin_folder . '-wp-styles', $this->_plugin_url . '/styles/wp-styles.css');
    }

    public function load_admin_scripts_css() {
        wp_enqueue_media();
        wp_enqueue_script('jquery');

        // wp_enqueue_script('jquery-ui-datepicker');
        // wp_enqueue_script('jquery-ui-sortable');
        // wp_enqueue_script('jquery-ui-tabs');

        // wp_enqueue_style( 'wp-color-picker' );

        // wp_enqueue_script(
        //     'wp-color-picker',
        //     admin_url( 'js/color-picker.min.js'),
        //     array( 'iris' ),
        //     false,
        //     1
        // );
    }
} // end of class

new TMCEFrontEnd(__FILE__);

