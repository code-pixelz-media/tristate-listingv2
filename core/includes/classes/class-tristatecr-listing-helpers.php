<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

/**
 * HELPER COMMENT START
 * 
 * This class is used to bring your plugin to life. 
 * All the other registered classed bring features which are
 * controlled and managed by this class.
 * 
 * Within the add_hooks() function, you can register all of 
 * your WordPress related actions and filters as followed:
 * 
 * add_action( 'my_action_hook_to_call', array( $this, 'the_action_hook_callback', 10, 1 ) );
 * or
 * add_filter( 'my_filter_hook_to_call', array( $this, 'the_filter_hook_callback', 10, 1 ) );
 * or
 * add_shortcode( 'my_shortcode_tag', array( $this, 'the_shortcode_callback', 10 ) );
 * 
 * Once added, you can create the callback function, within this class, as followed: 
 * 
 * public function the_action_hook_callback( $some_variable ){}
 * or
 * public function the_filter_hook_callback( $some_variable ){}
 * or
 * public function the_shortcode_callback( $attributes = array(), $content = '' ){}
 * 
 * 
 * HELPER COMMENT END
 */

/**
 * Class Tristatecr_Listing_Run
 *
 * Thats where we bring the plugin to life
 *
 * @package		TRISTATECRLISTING
 * @subpackage	Classes/Tristatecr_Listing_Run
 * @author		CodePixelz
 * @since		1.0.0
 */
class Tristatecr_Listing_Run
{

    /**
     * Our Tristatecr_Listing_Run constructor 
     * to run the plugin logic.
     *
     * @since 1.0.0
     */
    function __construct()
    {
        $this->add_hooks();
    }

    /**
     * ######################
     * ###
     * #### WORDPRESS HOOKS
     * ###
     * ######################
     */

    /**
     * Registers all WordPress and plugin related hooks
     *
     * @access	private
     * @since	1.0.0
     * @return	void
     */
    private function add_hooks()
    {

        add_action('plugin_action_links_' . TRISTATECRLISTING_PLUGIN_BASE, array($this, 'add_plugin_action_link'), 20);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_backend_scripts_and_styles'), 20);
        add_shortcode('trisate_cr_filter', array($this, 'tristate_cr_filter_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'tristate_cr_frontend_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'tristate_cr_frontend_styles'));
        add_filter('single_template', array($this, 'tristate_cr_frontend_styles'));
    }

    /**
     * ######################
     * ###
     * #### WORDPRESS HOOK CALLBACKS
     * ###
     * ######################
     */

    /**
     * Adds action links to the plugin list table
     *
     * @access	public
     * @since	1.0.0
     *
     * @param	array	$links An array of plugin action links.
     *
     * @return	array	An array of plugin action links.
     */
    public function add_plugin_action_link($links)
    {

        $links['our_shop'] = sprintf('<a href="%s" title="Github Link" style="font-weight:700;">%s</a>', 'https://github.com/code-pixelz-media/tristatecr-listing', __('Plugin Link', 'tristatecr-listing'));

        return $links;
    }

    /**
     * Enqueue the backend related scripts and styles for this plugin.
     * All of the added scripts andstyles will be available on every page within the backend.
     *
     * @access	public
     * @since	1.0.0
     *
     * @return	void
     */
    public function enqueue_backend_scripts_and_styles()
    {
        // wp_enqueue_style( 'tristatecrlisting-backend-styles', TRISTATECRLISTING_PLUGIN_URL . 'core/includes/assets/css/backend-styles.css', array(), TRISTATECRLISTING_VERSION, 'all' );
        // wp_enqueue_script( 'tristatecrlisting-backend-scripts', TRISTATECRLISTING_PLUGIN_URL . 'core/includes/assets/js/backend-scripts.js', array(), TRISTATECRLISTING_VERSION, false );
        // wp_localize_script( 'tristatecrlisting-backend-scripts', 'tristatecrlisting', array(
        // 	'plugin_name'   	=> __( TRISTATECRLISTING_NAME, 'tristatecr-listing' ),
        // ));
    }
    /**
     * Enqueue the frontend related scripts and styles for this plugin.
     *
     * @access	public
     * @since	1.0.0
     *
     * @return	void
     */

    public function tristate_cr_frontend_scripts()
    {

        wp_register_script('tristatecr-frontend-script', TRISTATECRLISTING_PLUGIN_URL . 'dist/main-script.js', array('jquery'), TRISTATECRLISTING_VERSION, true);
    }
    public function tristate_cr_frontend_styles()
    {

        wp_register_style('tristatecr-frontend-style', TRISTATECRLISTING_PLUGIN_URL . 'dist/main-style.css', array(), TRISTATECRLISTING_VERSION);
    }

    /**
     * Add a new menu item to the WordPress topbar
     *
     * @access	public
     * @since	1.0.0
     *
     * @param	object $admin_bar The WP_Admin_Bar object
     *
     * @return	void
     */
    public function add_admin_bar_menu_items($admin_bar)
    {

        $admin_bar->add_menu(array(
            'id'        => 'tristatecr-listing-id', // The ID of the node.
            'title'        => __('Demo Menu Item', 'tristatecr-listing'), // The text that will be visible in the Toolbar. Including html tags is allowed.
            'parent'    => false, // The ID of the parent node.
            'href'        => '#', // The 'href' attribute for the link. If 'href' is not set the node will be a text node.
            'group'        => false, // This will make the node a group (node) if set to 'true'. Group nodes are not visible in the Toolbar, but nodes added to it are.
            'meta'        => array(
                'title'        => __('Demo Menu Item', 'tristatecr-listing'), // The title attribute. Will be set to the link or to a div containing a text node.
                'target'    => '_blank', // The target attribute for the link. This will only be set if the 'href' argument is present.
                'class'        => 'tristatecr-listing-class', // The class attribute for the list item containing the link or text node.
                'html'        => false, // The html used for the node.
                'rel'        => false, // The rel attribute.
                'onclick'    => false, // The onclick attribute for the link. This will only be set if the 'href' argument is present.
                'tabindex'    => false, // The tabindex attribute. Will be set to the link or to a div containing a text node.
            ),
        ));

        $admin_bar->add_menu(array(
            'id'        => 'tristatecr-listing-sub-id',
            'title'        => __('My sub menu title', 'tristatecr-listing'),
            'parent'    => 'tristatecr-listing-id',
            'href'        => '#',
            'group'        => false,
            'meta'        => array(
                'title'        => __('My sub menu title', 'tristatecr-listing'),
                'target'    => '_blank',
                'class'        => 'tristatecr-listing-sub-class',
                'html'        => false,
                'rel'        => false,
                'onclick'    => false,
                'tabindex'    => false,
            ),
        ));
    }

    /**
     * Adds a shortcode for tristate
     *
     * @access	public
     * @since	1.0.0
     *
     *
     * @return	string
     */
    public function tristate_cr_filter_shortcode()
    {
        ob_start(); 
        require_once TRISTATECRLISTING_PLUGIN_DIR . 'core/includes/filterform.php';
   $output = ob_get_contents();  
   ob_get_clean();
   return $output;
    }
}
