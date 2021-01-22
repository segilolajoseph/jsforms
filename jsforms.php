<?php
/**
 * Plugin Name: JS Forms
 * Plugin URI:  https://github.com/segilolajoseph/jsforms
 * Description: WordPress form plugin. Use Drag & Drop form builder to create your forms.
 * Author:      Segilolaj
 * Version:     2.0.9
 * Text Domain: JSforms
 * Domain Path: /languages
 *
 *
 * @package    jsForms
 * @author     jsForms
 * @since      1.0.0
 */
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Don't allow multiple versions to be active
if (!class_exists('jsForms')) {

    /**
     * Main class.
     *
     * @since 1.0.0
     *
     * @package jsForms
     */
    final class jsForms {

        /**
         * One is the loneliest number that you'll ever do.
         *
         * @since 1.0.0
         *
         * @var object
         */
        private static $instance;

        /**
         * Plugin version.
         *
         * @since 1.0.0
         *
         * @var string
         */
        public $version = '2.0.9';

        /**
         * The form data handler instance.
         *
         * @since 1.0.0
         *
         * @var object jsForms_Form_Handler
         */
        public $form;

        /**
         * The front-end instance.
         *
         * @since 1.0.0
         *
         * @var object jsForms_Frontend
         */
        public $frontend;

        /**
         * The process instance.
         *
         * @since 1.0.0
         *
         * @var object jsForms_Submission_Entry
         */
        public $submission;

        /**
         * The smart tags instance.
         *
         * @since 1.0.0
         *
         * @var object
         */
        public $errors= array();
        
        /**
         * Set global options
         */
        public $options;
        
        /*
         * User model
         */
        public $user;
        
        public $plan;
        
        public $label;
        
        public $initial_login_status; // Stores login status before proceeding with the request
        
        /*
         * Holds enabled extension names
         */
        public $extensions= array();
        
        /**
         * Main Instance.
         *
         * Insures that only one instance of jsForms exists in memory at any one
         * time.
         *
         * @since 1.0.0
         *
         * @return jsForms
         */
        public static function instance() {

            if (!isset(self::$instance) && !( self::$instance instanceof jsForms )) {
                self::$instance = new jsForms;
                self::$instance->constants();
                self::$instance->includes();
                
                register_activation_hook(__FILE__, array(self::$instance, 'activation'));
                register_deactivation_hook(__FILE__, array(self::$instance, 'deactivation'));
                add_action('plugins_loaded', array(self::$instance, 'set_common_objects'));
            }
            return self::$instance;
        }
        
        /*
         * Invoked on activation
         */
        public function activation()
        {
            if (is_multisite()) { 
                global $wpdb;
                foreach ($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $blog_id) {
                    switch_to_blog($blog_id);
                    jsForms_Options::create_default_options();
                    restore_current_blog();
                } 

            } else {
                jsForms_Options::create_default_options();
            }
            do_action( 'jsF_installed' );

        }
        
        /*
         * Invoked on Deactivation
         */
        public function deactivation(){
            wp_clear_scheduled_hook('jsF_submission_report');
        }

        /**
         * Setup plugin constants.
         *
         * @since 1.0.0
         */
        private function constants() {
            // Plugin version.
            if (!defined('jsFORMS_VERSION')) {
                define('jsFORMS_VERSION', $this->version);
            }

            // Plugin Folder Path.
            if (!defined('jsFORMS_PLUGIN_DIR')) {
                define('jsFORMS_PLUGIN_DIR', plugin_dir_path(__FILE__));
            }

            // Plugin Folder URL.
            if (!defined('jsFORMS_PLUGIN_URL')) {
                define('jsFORMS_PLUGIN_URL', plugin_dir_url(__FILE__));
            }

            // Plugin Root File.
            if (!defined('jsFORMS_PLUGIN_FILE')) {
                define('jsFORMS_PLUGIN_FILE', __FILE__);
            }
            
            // Forms Post Type
            if (!defined('jsFORMS_FORM_POST_TYPE')) {
                define('jsFORMS_FORM_POST_TYPE', 'jsForms');
            }
            
             // Payment Status
            if (!defined('jsFORMS_COMPLETED')) {
                define('jsFORMS_COMPLETED', 'completed');
            }
            if (!defined('jsFORMS_PENDING')) {
                define('jsFORMS_PENDING', 'pending');
            }
            if (!defined('jsFORMS_HOLD')) {
                define('jsFORMS_HOLD', 'hold');
            }
            if (!defined('jsFORMS_DECLINED')) {
                define('jsFORMS_DECLINED', 'declined');
            }
            if (!defined('jsFORMS_REFUNDED')) {
                define('jsFORMS_REFUNDED', 'refunded');
            }
            if (!defined('jsFORMS_CANCELED')) {
                define('jsFORMS_CANCELED', 'canceled');
            }
        }

        /**
         * Loads the plugin language files.
         *
         * @since 1.0.0
         */
        public function load_textdomain() {
            load_plugin_textdomain('jsForms', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }
        
     
        /**
         * Include files.
         *
         * @since 1.0.0
         */
        private function includes() {

            // Global includes.
             require_once jsFORMS_PLUGIN_DIR . 'includes/functions.php';
             require_once jsFORMS_PLUGIN_DIR . 'includes/class-install.php';
             require_once jsFORMS_PLUGIN_DIR . 'includes/class-post.php';
             require_once jsFORMS_PLUGIN_DIR . 'includes/class-term.php';
             require_once jsFORMS_PLUGIN_DIR . 'includes/class-form.php';
             require_once jsFORMS_PLUGIN_DIR . 'includes/class-submission.php';
             require_once jsFORMS_PLUGIN_DIR . 'includes/class-frontend.php';
             require_once jsFORMS_PLUGIN_DIR . 'includes/class-validator.php';
             require_once jsFORMS_PLUGIN_DIR . 'includes/class-validation.php';
             require_once jsFORMS_PLUGIN_DIR . 'includes/class-user.php';
             require_once jsFORMS_PLUGIN_DIR . 'includes/class-options.php';
             require_once jsFORMS_PLUGIN_DIR . 'includes/class-form-widget.php';
             require_once jsFORMS_PLUGIN_DIR . 'includes/class-login-widget.php';
             require_once jsFORMS_PLUGIN_DIR . 'includes/class-emails.php';
             require_once jsFORMS_PLUGIN_DIR . 'includes/class-submission-export.php';
             require_once jsFORMS_PLUGIN_DIR . 'includes/class-submission-formatter.php';
             require_once jsFORMS_PLUGIN_DIR . 'includes/class-do-actions.php';
             require_once jsFORMS_PLUGIN_DIR . 'includes/class-label.php';
             require_once jsFORMS_PLUGIN_DIR . 'includes/class-form-render.php';
             
             if( is_admin()){
                require_once jsFORMS_PLUGIN_DIR . 'includes/admin/class-list-table.php';
                require_once jsFORMS_PLUGIN_DIR . 'includes/admin/class-list-cards.php';
                require_once jsFORMS_PLUGIN_DIR . 'includes/admin/class-admin.php';
                require_once jsFORMS_PLUGIN_DIR . 'includes/admin/help/class-help.php';
                require_once jsFORMS_PLUGIN_DIR . 'includes/admin/addon/class-addon.php';
                require_once jsFORMS_PLUGIN_DIR . 'includes/admin/overview/class-form-overview.php';
                require_once jsFORMS_PLUGIN_DIR . 'includes/admin/form/class-form-dashboard.php';
                require_once jsFORMS_PLUGIN_DIR . 'includes/admin/settings/class-settings.php';
                require_once jsFORMS_PLUGIN_DIR . 'includes/admin/submission/class-submission.php';
                require_once jsFORMS_PLUGIN_DIR . 'includes/admin/analytics/class-analytics.php';
                require_once jsFORMS_PLUGIN_DIR . 'includes/admin/labels/class-label.php';
                require_once jsFORMS_PLUGIN_DIR . 'includes/admin/tools/class-tools.php';
             }
        }

        /**
         * Setup common objects.
         *
         * @since 1.0.0
         */
        public function set_common_objects() {
            $this->load_textdomain();
            // Global objects.
            $this->options = jsForms_Options::instance();  
            $this->form = jsForms_Form::get_instance();
            $this->frontend   = jsForms_Frontend::get_instance();
            $this->submission    = jsForms_Submission::get_instance();
            $this->user    =  jsForms_User::get_instance();
            $this->label= jsForms_Label::get_instance();
            $this->initial_login_status = is_user_logged_in();
            // Hook now that all the stuff is loaded.
            do_action('jsForms_loaded');
        }

    }

    /**
     * Returns one instance
     *
     * @since 1.0.0
     * @return object
     */
    function jsForms() {
        return jsForms::instance();
    }

    jsForms();
} // End if().
