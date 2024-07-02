<?php

/**
 * Tristate Commercial Listing
 *
 * @package       TRISTATECRLISTING
 * @author        CodePixelz
 * @license       gplv2
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   Tristatecr Listing V2
 * Plugin URI:    https://tristatecr.com/
 * Description:   Tristate Commercial property listings filters.
 * Version:       1.0.0
 * Author:        CodePixelz
 * Author URI:    https://codepixelzmedia.com.np/
 * Text Domain:   tristatecr-listing
 * Domain Path:   /languages
 * License:       GPLv2
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with Tristate Commercial Listing. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

/**
 * HELPER COMMENT START
 * 
 * This file contains the main information about the plugin.
 * It is used to register all components necessary to run the plugin.
 * 
 * The comment above contains all information about the plugin 
 * that are used by WordPress to differenciate the plugin and register it properly.
 * It also contains further PHPDocs parameter for a better documentation
 * 
 * The function TRISTATECRLISTING() is the main function that you will be able to 
 * use throughout your plugin to extend the logic. Further information
 * about that is available within the sub classes.
 * 
 * HELPER COMMENT END
 */

// Plugin name
define('TRISTATECRLISTING_NAME','Tristate Commercial Listing');

// Plugin version
define('TRISTATECRLISTING_VERSION','1.0.0');

// Plugin Root File
define('TRISTATECRLISTING_PLUGIN_FILE', __FILE__);

// Plugin base
define('TRISTATECRLISTING_PLUGIN_BASE',plugin_basename(TRISTATECRLISTING_PLUGIN_FILE));

// Plugin Folder Path
define('TRISTATECRLISTING_PLUGIN_DIR',plugin_dir_path(TRISTATECRLISTING_PLUGIN_FILE));

// Plugin Folder URL
define('TRISTATECRLISTING_PLUGIN_URL',plugin_dir_url(TRISTATECRLISTING_PLUGIN_FILE));



/**
 * Load the main class for the core functionality
 */
require_once TRISTATECRLISTING_PLUGIN_DIR . 'core/class-tristatecr-listing.php';
require_once TRISTATECRLISTING_PLUGIN_DIR . 'core/tirstatecr-cli-commands.php';
require_once TRISTATECRLISTING_PLUGIN_DIR . 'drt.php';
require_once TRISTATECRLISTING_PLUGIN_DIR . 'core/tristatecr-ajax-actions.php';
require_once TRISTATECRLISTING_PLUGIN_DIR . 'core/tristatecr-rest-api.php';
require_once TRISTATECRLISTING_PLUGIN_DIR . 'core/tristatecr-deactivate.php';   
require_once TRISTATECRLISTING_PLUGIN_DIR . 'core/includes/classes/class-tristatecr-listing-cpt-menus.php'; 

if (class_exists('Tristatecr_Listing_Cpt_Menus')) {
    $tristatecr_listing_cpt_menus = new Tristatecr_Listing_Cpt_Menus();
}

// function TRISTATECRLISTING()
// {
//     return Tristatecr_Listing::instance();
// }

// TRISTATECRLISTING();

/**
 * Overriding default single templates for brokers,properties and search
 */
function tristatecr_cpt_single_template($single_template)
{
    global $post;

    if ('brokers' === $post->post_type) {
        $single_template = plugin_dir_path(__FILE__) . '/core/single-brokers.php';
    }
    // if ('properties' === $post->post_type) {
    //     $single_template = plugin_dir_path(__FILE__) . '/core/single-properties.php';
    // }
/*     if ('properties_search' === $post->post_type) {
        $single_template = plugin_dir_path(__FILE__) . '/core/single-properties_search.php';
    } */


    return $single_template;
}

add_filter('single_template', 'tristatecr_cpt_single_template');

function my_plugin_override_single_template($template) {
    global $post;
    if (is_singular('properties')) {
        // Path to the custom template in the plugin directory
        $plugin_template = plugin_dir_path(__FILE__) . '/core/single-properties.php';
        
        // Check if the template file exists in the plugin directory
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }



 /*      if ('properties_search' === $post->post_type) {
        $single_template = plugin_dir_path(__FILE__) . '/core/single-properties_search.php';
    } */
    if ($post && 'properties_search' === $post->post_type) {
        $plugin_template = plugin_dir_path(__FILE__) . 'core/single-properties_search.php';

        
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }

    return $template;
}
add_filter('template_include', 'my_plugin_override_single_template');


/**
 * The function `tristate_cr_single_scripts` enqueues various CSS and JavaScript files for a WordPress
 * plugin, with conditional loading based on whether the current page is a single post or not.
 */
function tristate_cr_single_scripts()
{
  if ((is_singular(array('properties', 'properties_search', 'brokers')))  || (has_shortcode(get_post()->post_content, 'TSC-inventory-pub'))) {


    $settings = get_option('tristate_cr_settings');
    $get_google_map_api_key = $settings['google_maps_api_key'];

    wp_enqueue_script('jquery');

  /*   if (!wp_script_is('jquery', 'enqueued')) {
        wp_enqueue_script('jquery');
    } */

    wp_enqueue_script('jqueryui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js', array(), '1.0.0', true);
    //wp_enqueue_script('swiperjs', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.5/swiper-bundle.min.js', array(), '1.0.0', true);
    wp_enqueue_script('select2js-script', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array(), '1.0.0', true);

    wp_enqueue_script('single-scripts', TRISTATECRLISTING_PLUGIN_URL . 'core/includes/assets/js/frontend-scripts.js', array(), time(), true);
   

    wp_enqueue_style('jqueryuicss', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css', array(), '1.0.0');

    wp_enqueue_style('select2js-style', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '1.0.0');

    // if (!is_single()) {
        wp_register_script('traistate-google-map', 
        TRISTATECRLISTING_PLUGIN_URL . 'core/includes/assets/js/tristate-google-map.js', 
        array('jquery'), time(), true);
        wp_register_script('traistate-google-map-api', 'https://maps.googleapis.com/maps/api/js?key=' . $get_google_map_api_key . '&libraries=geometry&callback=initMap', array(), '1.0.0', true);
        
    // }

   wp_enqueue_style('single-styles', TRISTATECRLISTING_PLUGIN_URL . 'core/includes/assets/css/frontend-styles.css', array(), time());
    wp_enqueue_style('allfontawesome', TRISTATECRLISTING_PLUGIN_URL . 'core/includes/assets/css/all.min.css', array(), '1.0.0');
    //wp_enqueue_style('swipercss', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/6.8.4/swiper-bundle.min.css', array(), '1.0.0');
    
    
    if(is_single('properties') || is_singular('properties') ){
        wp_enqueue_script('swiperjs', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.5/swiper-bundle.min.js', array(), '1.0.0', true);
        wp_enqueue_style('swipercss', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/6.8.4/swiper-bundle.min.css', array(), '1.0.0');
    
         wp_enqueue_script('fancyboxjs', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js', array(), '1.0.0', true);
        wp_enqueue_style('fancyboxcss', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css', array(), '1.0.0');
    }

  //  wp_enqueue_style('single-styles', TRISTATECRLISTING_PLUGIN_URL . 'core/includes/assets/css/frontend-styles.css', array(), time());
   // wp_enqueue_script('single-scripts', TRISTATECRLISTING_PLUGIN_URL . 'core/includes/assets/js/frontend-scripts.js', array(), time(), true);




    
  }
}
add_action('wp_enqueue_scripts', 'tristate_cr_single_scripts',9999);







/**
 * The function `tristatecr_single_property_googe_map` generates an iframe displaying a Google Map with
 * a marker at the specified latitude and longitude coordinates.
 * 
 * @param $lat Latitude of the location for the Google Map.
 * @param $lng The `lng` parameter in the `tristatecr_single_property_googe_map` function represents the
 * longitude coordinate for a location on the map. It is used to specify the horizontal position of the
 * location.
 */
function tristatecr_single_property_googe_map($lat, $lng)
{ ?>
    <iframe width="800" height="600" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=<?php echo $lat; ?>,<?php echo $lng;   ?>&hl=es&z=14&output=embed&markers=https://s3.amazonaws.com/buildout-production/brandings/2138/profile_photo/small.png<?php echo $lat; ?>,<?php echo $lng; ?>">
    </iframe>


<?php
}


// $results = $wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = '_import_buildout_id'");
// $imported_ids = wp_list_pluck($results, 'meta_value', 'post_id');
// $filenames = array(
//    'https://docs.google.com/spreadsheets/d/1R0-lie_XfdirjxoaXZ59w4etaQPWFBD5c45i-5CaaMk/gviz/tq?tqx=out:csv&sheet=ny',
//    'https://docs.google.com/spreadsheets/d/1R0-lie_XfdirjxoaXZ59w4etaQPWFBD5c45i-5CaaMk/gviz/tq?tqx=out:csv&sheet=pa'
// );

//     foreach ($filenames as $fn) {
     
//         if (($handle = fopen($fn, "r")) !== FALSE) {
//             $row = 0;
//             while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
//                 $row++;

//                 // Header row
//                 if ($row == 1) {
//                     $header = $data;
//                     array_walk($header, function (&$item) {
//                         $item = sanitize_title($item);
//                         $item = strtolower(str_replace('-', '_', $item));
//                     });
//                     continue;
//                 }

//                 // Data row
//                 $item         = (object) array_combine($header, $data);
//                 $id             = new_np_generate_google_csv_item_id($item);
//                 $checksum = md5(json_encode($item));
//                 $message = "- Processing #$id";
               

//                 if ($buildout_id = $item->buildout_id ?? false) {
//                     $message = "-- Found Buildout ID $buildout_id for row";
                   
//                 } else {
//                     $message = "-- No Buildout ID found for row";
                   
                   
//                     continue;
//                 }

//                 // Find the imported post_id
//                 $post_ids = array_keys($imported_ids,$buildout_id);
//                // var_dump($post_ids);
//                 if (empty($post_ids)) {
//                     $message = "-- No post IDs found";
                   
                   
                    
//                 } else {
//                     $all_found_posts = join(',',$post_ids);
//                     $message = "-- Found posts ID's $all_found_posts";
                   
//                    //  $counter['found']++;
//                 }

//                 // Check the checksum
//                //  $post_sheet_checksum = $sheets_checksums[$post_id] ?? false;

//                //  if ($post_sheet_checksum && $post_sheet_checksum == $checksum) {
//                //      $message = "--- No changes detected, checksum $checksum matches.";
                   
//                //      $message = "--- Skipping.";
                   
//                //      continue;
//                //  } else {
//                //      $message = "--- Changes detected, checksum $checksum does not match $post_sheet_checksum";
                   
//                //  }

//                 $sheet_meta = new_np_process_google_csv_item_meta($item);
                
//                 if(!empty($post_ids)){
                
//                     foreach($post_ids as $pd){
//                         $message = "--- Updating post_meta for post_id:$pd buildout_id:$buildout_id";
                       
                       
//                         foreach ($sheet_meta as $key => $value) {
//                             $message = "---- Updating $key to $value";
                           
//                             update_post_meta($pd, $key, $value);
    
//                             if ($key == '_gsheet_min_size') {
//                                 $new_min_val = (float) preg_replace('/[^0-9.]/', '', $value);
//                                 update_post_meta($pd, '_gsheet_min_size_fm', $new_min_val);
//                             }
//                             if ($key == '_gsheet_max_size') {
//                                 $new_max_val = (float) preg_replace('/[^0-9.]/', '', $value);
//                                 update_post_meta($pd, '_gsheet__max_size_fm', $new_max_val);
//                             }
//                             if ($key == '_gsheet_state') {
//                                 update_post_meta($pd, '_gsheet_state', strtoupper($value));
//                             }
//                             if ($key == '_gsheet_monthly_rent') {
//                                 $newmnthrent = (float) preg_replace('/[^0-9.]/', '', $value);
//                                 update_post_meta($pd, '__gsheet__monthly_rent', $newmnthrent);
//                             }
    
//                             update_post_meta($pd, '_gsheet_last_updated', time());
//                         }
//                         $message = "---- Updated sheet data to # $pd";
                       
//                     }
                  
//                 }

               
//             }
//             fclose($handle);
//         }

//         $message = 'Found ' . $row . ' records in ' . $fn . '.';
//         NEW_np_log($message);
//     }