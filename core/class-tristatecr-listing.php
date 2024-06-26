<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

/**
 * HELPER COMMENT START
 * 
 * This is the main class that is responsible for registering
 * the core functions, including the files and setting up all features. 
 * 
 * To add a new class, here's what you need to do: 
 * 1. Add your new class within the following folder: core/includes/classes
 * 2. Create a new variable you want to assign the class to (as e.g. public $helpers)
 * 3. Assign the class within the instance() function ( as e.g. self::$instance->helpers = new Tristatecr_Listing_Helpers();)
 * 4. Register the class you added to core/includes/classes within the includes() function
 * 
 * HELPER COMMENT END
 */

//require_once TRISTATECRLISTING_PLUGIN_DIR . 'core/includes/classes/class-tristatecr-listing-cpt-menus.php';

if (!class_exists('Tristatecr_Listing')) :

    /**
     * Main Tristatecr_Listing Class.
     *
     * @package		TRISTATECRLISTING
     * @subpackage	Classes/Tristatecr_Listing
     * @since		1.0.0
     * @author		CodePixelz
     */
    final class Tristatecr_Listing
    {

        /**
         * The real instance
         *
         * @access	private
         * @since	1.0.0
         * @var		object|Tristatecr_Listing
         */
        private static $instance;

        /**
         * TRISTATECRLISTING helpers object.
         *
         * @access	public
         * @since	1.0.0
         * @var		object|Tristatecr_Listing_Helpers
         */
        public $helpers;

        /**
         * TRISTATECRLISTING settings object.
         *
         * @access	public
         * @since	1.0.0
         * @var		object|Tristatecr_Listing_Settings
         */
        public $settings;

        /**
         * TRISTATECRLISTING Custom post type object.
         *
         * @access	public
         * @since	1.0.0
         * @var		object|Tristatecr_Listing_Cpt_Menus
         */
        public $cpt_menus;

        /**
         * Throw error on object clone.
         *
         * Cloning instances of the class is forbidden.
         *
         * @access	public
         * @since	1.0.0
         * @return	void
         */
        public function __clone()
        {
            _doing_it_wrong(__FUNCTION__, __('You are not allowed to clone this class.', 'tristatecr-listing'), '1.0.0');
        }

        /**
         * Disable unserializing of the class.
         *
         * @access	public
         * @since	1.0.0
         * @return	void
         */
        public function __wakeup()
        {
            _doing_it_wrong(__FUNCTION__, __('You are not allowed to unserialize this class.', 'tristatecr-listing'), '1.0.0');
        }

        /**
         * Main Tristatecr_Listing Instance.
         *
         * Insures that only one instance of Tristatecr_Listing exists in memory at any one
         * time. Also prevents needing to define globals all over the place.
         *
         * @access		public
         * @since		1.0.0
         * @static
         * @return		object|Tristatecr_Listing	The one true Tristatecr_Listing
         */
        public static function instance()
        {
            if (!isset(self::$instance) && !(self::$instance instanceof Tristatecr_Listing)) {
                self::$instance                    = new Tristatecr_Listing;
                self::$instance->base_hooks();
                self::$instance->includes();
                // self::$instance->helpers        = new Tristatecr_Listing_Helpers();
                // self::$instance->settings        = new Tristatecr_Listing_Settings();
                self::$instance->posttypes        = new Tristatecr_Listing_Cpt_Menus();
                //Fire the plugin logic
                new Tristatecr_Listing_Run();

                /**
                 * Fire a custom action to allow dependencies
                 * after the successful plugin setup
                 */
                do_action('TRISTATECRLISTING/plugin_loaded');
            }

            return self::$instance;
        }

        /**
         * Include required files.
         *
         * @access  private
         * @since   1.0.0
         * @return  void
         */
        private function includes()
        {
             require_once TRISTATECRLISTING_PLUGIN_DIR . 'core/includes/classes/class-tristatecr-listing-helpers.php';
            // require_once TRISTATECRLISTING_PLUGIN_DIR . 'core/includes/classes/class-tristatecr-listing-settings.php';
            // require_once TRISTATECRLISTING_PLUGIN_DIR . 'core/includes/classes/class-tristatecr-listing-run.php';
            require_once TRISTATECRLISTING_PLUGIN_DIR . 'core/includes/classes/class-tristatecr-listing-cpt-menus.php';
        }

        /**
         * Add base hooks for the core functionality
         *
         * @access  private
         * @since   1.0.0
         * @return  void
         */
        private function base_hooks()
        {
            add_action('plugins_loaded', array(self::$instance, 'load_textdomain'));
        }

        /**
         * Loads the plugin language files.
         *
         * @access  public
         * @since   1.0.0
         * @return  void
         */
        public function load_textdomain()
        {
            load_plugin_textdomain('tristatecr-listing', FALSE, dirname(plugin_basename(TRISTATECRLISTING_PLUGIN_FILE)) . '/languages/');
        }
    }

endif; // End if class_exists check.