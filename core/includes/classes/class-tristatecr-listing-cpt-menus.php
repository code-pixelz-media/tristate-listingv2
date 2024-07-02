<?php

/**
 * Class Tristatecr_Listing_Cpt_Menus
 *
 * This class manages the plugin settings and custom post type
 *
 * @package		TRISTATECRLISTING
 * @subpackage	Classes/Tristatecr_Listing_Cpt_Menus
 * @author		CodePixelz
 * @since		1.0.0
 */
class Tristatecr_Listing_Cpt_Menus
{

    /**
     * Tristatecr_Listing_Cpt_Menus constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        add_action('init', array($this, 'trs_register_custom_post_type'));
        add_action('admin_menu', array($this, 'trs_add_plugin_settings'));
        add_action('admin_init', array($this, 'trs_register_settings'));
    }

    /**
     * Registers custom post type called 'property'
     *
     * @since 1.0.0
     */
    public function trs_register_custom_post_type()
    {
        $properties_labels = array(
            'name'                  => _x('Properties', 'Post Type General Name', 'tristatecr-listing'),
            'singular_name'         => _x('Property', 'Post Type Singular Name', 'tristatecr-listing'),
            'menu_name'             => __('Properties', 'tristatecr-listing'),
            'all_items'             => __('All Properties', 'tristatecr-listing'),
            'add_new_item'          => __('Add New Property', 'tristatecr-listing'),
            'add_new'               => __('Add New Property', 'tristatecr-listing'),
            'new_item'              => __('New Property', 'tristatecr-listing'),
            'edit_item'             => __('Edit Property', 'tristatecr-listing'),
            'update_item'           => __('Update Property', 'tristatecr-listing'),
            'view_item'             => __('View Property', 'tristatecr-listing'),
            'search_items'          => __('Search Property', 'tristatecr-listing'),
            'not_found'             => __('Not found', 'tristatecr-listing'),
            'not_found_in_trash'    => __('Not found in Trash', 'tristatecr-listing'),
        );

        $properties_args = array(
            'label'                 => __('Properties', 'tristatecr-listing'),
            'description'           => __('Properties Description', 'tristatecr-listing'),
            'labels'                => $properties_labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'public'                => true,
            'menu_icon'             => 'dashicons-admin-multisite',
            'has_archive'           => true,
            'rewrite'               => array('slug' => 'properties'),
            'menu_position'         => 8,
            'show_in_rest' => true,
        );


        register_post_type('properties', $properties_args);

        $search_args = array(
            'public'                 => true,
            'show_in_rest' => true,
            'label'                 => __('Searches', 'textdomain'),
            'menu_icon'         => 'dashicons-search',
            'has_archive'     => false,
            'rewrite'             => array('slug' => 'properties_search'),
            'menu_position' => 5,
            'show_in_menu'     => 'edit.php?post_type=properties',
            'supports'             => array('title', 'author', 'custom-fields'),
        );

        register_post_type('properties_search', $search_args);

        $broker_args = array(
            'public'                 => true,
            'show_in_rest' => true,
            'label'                 => __('Brokers', 'textdomain'),
            'menu_icon'         => 'dashicons-search',
            'has_archive'     => false,
            'rewrite'             => array('slug' => 'brokers'),
            'menu_position' => 5,
            'show_in_menu'     => 'edit.php?post_type=properties',
            'supports'             => array('title', 'author', 'custom-fields'),
        );

        register_post_type('brokers', $broker_args);
    }

    /**
     * Adds Sub Menu to the property custom post type
     *
     * @since 1.0.0
     */
    public function trs_add_plugin_settings()
    {
        add_submenu_page(
            'edit.php?post_type=properties',
            'Property Settings',
            'Settings',
            'manage_options',
            'tristate-cr-settings',
            array($this, 'trs_create_admin_page')
        );
    }

    /**
     * Callback function to create the admin settings page
     *
     * @since 1.0.0
     */
    public function trs_create_admin_page()
    {
?>
        <div class="wrap">
            <form method="post" action="options.php">
                <?php
                settings_fields('tristate_cr_settings_group');
                do_settings_sections('tristate_cr_settings');
                submit_button('Save Settings');
                ?>
            </form>
        </div>
<?php
    }

    /**
     * Registers plugin settings
     *
     * @since 1.0.0
     */
    public function trs_register_settings()
    {
        // Register a single settings group for all fields
        register_setting('tristate_cr_settings_group', 'tristate_cr_settings', array($this, 'trs_settings_sanitize'));

        // Add settings section
        add_settings_section('tristate_cr_settings_section', 'API Settings', array($this, 'trs_settings_section_callback'), 'tristate_cr_settings');

        // Add fields for Google Maps API key
        add_settings_field('google_maps_api_key', 'Google Maps API Key:', array($this, 'trs_google_maps_api_key_callback'), 'tristate_cr_settings', 'tristate_cr_settings_section');

        // Add fields for Buildout API key
        add_settings_field('buildout_api_key', 'Buildout API Key:', array($this, 'trs_buildout_api_key_callback'), 'tristate_cr_settings', 'tristate_cr_settings_section');
        
        //Add field to for spreadsheet id
        add_settings_field('spreadsheet_id', 'Spreadsheet Id:', array($this, 'trs_spreadsheet_id_callback'), 'tristate_cr_settings', 'tristate_cr_settings_section');
        
         //Add field to for spreadsheet id
         add_settings_field('main_filter_page', 'Main Filter Page:', array($this, 'trs_main_filter_page_callback'), 'tristate_cr_settings', 'tristate_cr_settings_section');
    }

    /**
     * Sanitize callback function for settings
     *
     * @param $input
     * @return array
     * @since 1.0.0
     */
    public function trs_settings_sanitize($input)
    {
        $sanitized_input = array();

        // Sanitize each input field
        if (isset($input['google_maps_api_key'])) {
            $sanitized_input['google_maps_api_key'] = sanitize_text_field($input['google_maps_api_key']);
        }

        if (isset($input['buildout_api_key'])) {
            $sanitized_input['buildout_api_key'] = sanitize_text_field($input['buildout_api_key']);
        }
        
        if (isset($input['spreadsheet_id'])) {
            $sanitized_input['spreadsheet_id'] = sanitize_text_field($input['spreadsheet_id']);
        }
        
        if (isset($input['main_filter_page'])) {
            $sanitized_input['main_filter_page'] = sanitize_text_field($input['main_filter_page']);
        }


        return $sanitized_input;
    }

    /**
     * Callback function for settings section
     *
     * @since 1.0.0
     */
    public function trs_settings_section_callback()
    {
        // This function intentionally left blank
    }

    /**
     * Callback function for Google Maps API key field
     *
     * @since 1.0.0
     */
    public function trs_google_maps_api_key_callback()
    {
        $settings = get_option('tristate_cr_settings');
        $google_maps_api_key = isset($settings['google_maps_api_key']) ? $settings['google_maps_api_key'] : '';
        echo '<input type="text" class="regular-text" name="tristate_cr_settings[google_maps_api_key]" value="' . esc_attr($google_maps_api_key) . '" />';
    }

    /**
     * Callback function for Buildout API key field
     *
     * @since 1.0.0
     */
    public function trs_buildout_api_key_callback()
    {
        $settings = get_option('tristate_cr_settings');
        $buildout_api_key = isset($settings['buildout_api_key']) ? $settings['buildout_api_key'] : '';
        echo '<input type="text" class="regular-text" name="tristate_cr_settings[buildout_api_key]" value="' . esc_attr($buildout_api_key) . '" />';
    }
    
    public function trs_spreadsheet_id_callback(){
        $settings = get_option('tristate_cr_settings');
        $spreadsheet_id = isset($settings['spreadsheet_id']) ? $settings['spreadsheet_id'] : '';
        echo '<input type="text" class="regular-text" name="tristate_cr_settings[spreadsheet_id]" value="' . esc_attr($spreadsheet_id) . '" />';
    }
    
    
    public function trs_main_filter_page_callback(){
    
        $settings = get_option('tristate_cr_settings');
        $selected =  isset($settings['main_filter_page']) ? $settings['main_filter_page'] : '';
        wp_dropdown_pages( array(
            'name' => 'tristate_cr_settings[main_filter_page]',
            'selected' => $selected,
        ) );
    }
}
