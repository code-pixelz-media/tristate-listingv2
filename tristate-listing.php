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
define('TRISTATECRLISTING_NAME',            'Tristate Commercial Listing');

// Plugin version
define('TRISTATECRLISTING_VERSION',        '1.0.0');

// Plugin Root File
define('TRISTATECRLISTING_PLUGIN_FILE',    __FILE__);

// Plugin base
define('TRISTATECRLISTING_PLUGIN_BASE',    plugin_basename(TRISTATECRLISTING_PLUGIN_FILE));

// Plugin Folder Path
define('TRISTATECRLISTING_PLUGIN_DIR',    plugin_dir_path(TRISTATECRLISTING_PLUGIN_FILE));

// Plugin Folder URL
define('TRISTATECRLISTING_PLUGIN_URL',    plugin_dir_url(TRISTATECRLISTING_PLUGIN_FILE));



/**
 * Load the main class for the core functionality
 */
require_once TRISTATECRLISTING_PLUGIN_DIR . 'core/class-tristatecr-listing.php';

// require_once TRISTATECRLISTING_PLUGIN_DIR . 'demo/cli-commands.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @author  CodePixelz
 * @since   1.0.0
 * @return  object|Tristatecr_Listing
 */
function TRISTATECRLISTING()
{
    return Tristatecr_Listing::instance();
}

TRISTATECRLISTING();


// $f_name = 'https://docs.google.com/spreadsheets/d/1R0-lie_XfdirjxoaXZ59w4etaQPWFBD5c45i-5CaaMk/gviz/tq?tqx=out:csv&sheet=0';

// if (($handle = fopen($f_name, "r")) !== FALSE) {
// 	$row = 0;
// 	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
// 		$row++;
// 		if ($row == 1) {
// 			$header = $data;
// 			array_walk($header, function(&$item) {
// 				$item = sanitize_title( $item );
// 				$item = strtolower( str_replace('-', '_', $item) );
// 			});
// 			continue;
// 		}

// 		// Data row
// 		$item 		=  array_combine($header, $data);
// 		// print_r($item);

// 		// $id 			= ($item);
// 		// $checksum = md5( json_encode( $item ) );
// 		// $message = "- Processing #$id";
// 	}
// }

// // die();
function custom_brokers_template($single_template)
{
    global $post;

    if ('brokers' === $post->post_type) {
        $single_template = plugin_dir_path(__FILE__) . '/core/single-brokers.php';
    }
    if ('properties' === $post->post_type) {
        $single_template = plugin_dir_path(__FILE__) . '/core/single-properties.php';
    }

    return $single_template;
}

add_filter('single_template', 'custom_brokers_template');

/**
 * Proper way to enqueue scripts and styles
 */
function tristate_cr_single_scripts()
{
    wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'single-scripts', TRISTATECRLISTING_PLUGIN_URL. 'core/includes/assets/js/frontend-scripts.js', array(), time() ,true);
    wp_enqueue_script( 'jqueryui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js', array(), time() ,true);
	wp_enqueue_script( 'swiperjs', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/6.8.4/swiper-bundle.min.js', array(), time() ,true);
    wp_enqueue_script('select2js-script', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array(), time(), true);

    wp_enqueue_style('jqueryuicss', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css', array(), time());
    wp_enqueue_style('single-styles', TRISTATECRLISTING_PLUGIN_URL . 'core/includes/assets/css/frontend-styles.css', array(), time());
    
    wp_enqueue_style('select2js-style', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), time());

   

	wp_enqueue_style( 'single-styles', TRISTATECRLISTING_PLUGIN_URL. 'core/includes/assets/css/frontend-styles.css', array(), time() );
	wp_enqueue_style( 'allfontawesome', TRISTATECRLISTING_PLUGIN_URL. 'core/includes/assets/css/all.min.css', array(), time() );
	wp_enqueue_style( 'swipercss', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/6.8.4/swiper-bundle.min.css', array(), time() );

}
add_action('wp_enqueue_scripts', 'tristate_cr_single_scripts');
