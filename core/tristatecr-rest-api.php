<?php

if (!defined('ABSPATH')) {
	exit;
}

// Check if the nonce matches
function tristatecr_has_private_data_access()
{
	return is_user_logged_in();
	if (!isset($_REQUEST['wp_rest'])) {
		return;
	}
	if (!wp_verify_nonce($_REQUEST['wp_rest'], 'wp_rest')) {
		return;
	} else {
		return true;
	}
}

// Register WP REST API routes
add_action('rest_api_init', 'tristatectr_rest_api_init');

function tristatectr_rest_api_init()
{
	register_rest_route('tristatectr/v1', '/listings', array(
		'methods' => 'GET',
		'callback' => 'tristatectr_rest_listings',
	));
	register_rest_route('tristatectr/v1', '/brokers', array(
		'methods' => 'GET',
		'callback' => 'tristatectr_rest_brokers',
	));
	register_rest_route('tristatectr/v2', '/brokers', array(
		'methods' => 'GET',
		'callback' => 'tristatectr_rest_brokers_v2',
	));
	
	register_rest_route('tristatectr/v2', '/agentsdropdown', array(
		'methods' => 'GET',
		'callback' => 'get_agents_dropdown_cb_callback_rest',
	));
}

function tristatectr_rest_listings($data)
{
	$posts = get_posts(array(
		'post_type' => 'properties',
		'posts_per_page' => -1,
	));

	if (empty($posts)) {
		return null;
	}

	$results = array();
	foreach ($posts as $post) {

		// title: _buildout_sale_listing_web_title | _buildout_sale_title | _gsheet_address | the_title
		// subtitle: _buildout_city, _buildout_county, _buildout_state
		// badges: _gsheet_use, _gsheet_listing_type, _gsheet_price_sf, _gsheet_commission
		// summary: _buildout_location_description | _gsheet_notes
		// min_size: _gsheet_min_size
		// max_size: _gsheet_max_size
		// zoning: _buildout_zoning
		// key_tag: _gsheet_key_tag
		// agents: _buildout_broker_ids | _gsheet_listing_agent
		// lease_out: _gsheet_lease_out
		// lease_conditions: _gsheet_lease_conditions
		// price: _gsheet_monthly_rent | _gsheet_asking_price
		// more_info: _gsheet_link_to_more_info
		// 3dtour: _gsheet_3d_tour

		$ID 							= $post->ID;
		$buildout_id 			= (int) get_post_meta($ID, '_buildout_id', true);
		$title 						= get_post_meta($ID, '_buildout_sale_listing_web_title', true);
		$subtitle 				= implode(', ', array(get_post_meta($ID, '_buildout_city', true), get_post_meta($ID, '_buildout_county', true), get_post_meta($ID, '_buildout_state', true)));
		$badges 					= array(
			'use' 				=> get_post_meta($ID, '_gsheet_use', true),
			'type' 				=> get_post_meta($ID, '_gsheet_listing_type', true),
			'price_sf' 		=> get_post_meta($ID, '_gsheet_price_sf', true),
			'commission' 	=> get_post_meta($ID, '_gsheet_commission', true)
		);
		$_use 						= get_post_meta($ID, '_gsheet_use', true);
		$_type 						= get_post_meta($ID, '_gsheet_listing_type', true);
		$_price_sf 				= get_post_meta($ID, '_gsheet_price_sf', true);
		$_price_sf 				= preg_replace('/\.[0-9]+/', '', $_price_sf);
		$_price_sf 				= (int) preg_replace('/[^0-9]/', '', $_price_sf);
		$_commission 			= get_post_meta($ID, '_gsheet_commission', true);
		$summary 					= get_post_meta($ID, '_buildout_location_description', true);
		$min_size 				= get_post_meta($ID, '_gsheet_min_size', true);
		$max_size 				= get_post_meta($ID, '_gsheet_max_size', true);
		$size 						= $min_size ?? $max_size;
		$size 						= preg_replace('/\.[0-9]+/', '', $size);
		$size 						= (int) preg_replace('/[^0-9]/', '', $size);
		$zoning 					= get_post_meta($ID, '_buildout_zoning', true);
		$key_tag 					= get_post_meta($ID, '_gsheet_key_tag', true);
		$agents 					= (array) tristatectr_get_brokers_with_excluded(get_post_meta($ID, '_buildout_broker_ids', true));
		$_agent 					= get_post_meta($ID, '_gsheet_listing_agent', true);
		$lease_out 				= get_post_meta($ID, '_gsheet_lease_out', true);

		$lease_conditions = get_post_meta($ID, '_buildout_lease_description', true);
		$lease_conditions = get_post_meta($ID, '_gsheet_lease_conditions', true);

		$bo_price 				= get_post_meta($ID, '_buildout_sale_price_dollars', true);
		$price 						= get_post_meta($ID, '_gsheet_monthly_rent', true);
		// Remove fractional units from the price
		$_price 					= preg_replace('/\.[0-9]+/', '', $price);
		// Convert the price to integer value
		$_price = (int) preg_replace('/[^0-9]/', '', $_price);
		$more_info 				= get_post_meta($ID, '_gsheet_link_to_more_info', true);
		$more_info 				= get_post_meta($ID, '_buildout_sale_listing_url', true) ?? get_post_meta($ID, '_buildout_lease_listing_url', true);
		$tour3d 					= get_post_meta($ID, '_gsheet_3d_tour', true);
		$tour3d 					= get_post_meta($ID, '_buildout_matterport_url', true);
		$youtube_url 			= get_post_meta($ID, '_buildout_you_tube_url', true);
		$zip 							= get_post_meta($ID, '_gsheet_zip', true) ?? get_post_meta($ID, '_buildout_zip', true);
		$neighborhood 		= get_post_meta($ID, '_gsheet_neighborhood', true);
		$vented 					= get_post_meta($ID, '_gsheet_vented', true);
		$city 						= get_post_meta($ID, '_buildout_city', true);
		$borough 					= get_post_meta($ID, '_gsheet_borough', true);
		$state 						= get_post_meta($ID, '_gsheet_state', true);

		$image 						= false;
		if ($photos = get_post_meta($ID, '_buildout_photos', true)) {
			$photo = reset($photos);
			$image = $photo->formats->thumb ?? '';
		}

		$lat 							= get_post_meta($ID, '_buildout_latitude', true);
		$lng 							= get_post_meta($ID, '_buildout_longitude', true);

		$buildout_notes 	= get_post_meta($ID, '_buildout_notes', true);
		$gsheet_notes 		= get_post_meta($ID, '_gsheet_notes', true);

		$buildout_synced 	= get_post_meta($ID, '_buildout_last_updated', true) ?? false;
		$sheets_synced 		= get_post_meta($ID, '_gsheet_last_updated', true) ?? false;

		$results[] = array(
			'id' 						=> $ID,
			'buildout_id' 	=> $buildout_id,
			'title' 				=> $title,
			'subtitle' 			=> $subtitle,
			'badges' 				=> $badges,
			'_use' 					=> $_use,
			'_type' 				=> $_type,
			'_price_sf' 		=> $_price_sf,
			'_commission' 	=> $_commission,
			'summary' 			=> $summary,
			'min_size' 			=> $min_size,
			'max_size' 			=> $max_size,
			'size' 					=> $size,
			'zoning' 				=> $zoning,
			'key_tag' 			=> tristatecr_has_private_data_access() ? $key_tag : 'Log in to view',
			'agents' 				=> $agents,
			'_agent' 				=> array_keys($agents)[0],
			'lease_out' 		=> tristatecr_has_private_data_access() ? $lease_out : 'Log in to view',
			'lease_conditions' => tristatecr_has_private_data_access() ? $lease_conditions : 'Log in to view',
			'price' 				=> $price,
			'_price' 				=> $_price,
			'rent' 					=> $_price,
			'bo_price' 			=> $bo_price,
			'more_info' 		=> $more_info,
			'tour3d' 				=> $tour3d,
			'youtube_url' 	=> $youtube_url,
			'zip' 					=> $zip,
			'neighborhood' 	=> $neighborhood,
			'vented' 				=> $vented,
			'city' 					=> $city,
			'borough' 			=> $borough,
			'state' 				=> $state,

			'image' 				=> $image,

			'lat' 					=> $lat,
			'lng' 					=> $lng,

			'buildout_notes' 	=> tristatecr_has_private_data_access() ? $buildout_notes : 'Log in to view',
			'gsheet_notes' 		=> tristatecr_has_private_data_access() ? $gsheet_notes : 'Log in to view',

			'buildout_synced' 	=> tristatecr_has_private_data_access() ? (int) $buildout_synced : false,
			'sheets_synced' 		=> tristatecr_has_private_data_access() ? (int) $sheets_synced : false,
			'get_page_link'   => get_permalink($ID),
		);
	}

	return $results;
}

function tristatectr_rest_brokers()
{
	$brokers = get_option('tristatecr_datasync_brokers') ?? array();
	return (array) $brokers;
}

function tristatectr_rest_brokers_v2()
{
	$brokers = get_option('tristatecr_datasync_brokers') ?? array();
	$results = array();
	foreach ($brokers as $key => $value) {
		$results[] = array(
			'id' => $key,
			'name' => $value,
		);
	}
	return (array) $results;
}


function get_agents_dropdown_cb_callback_rest($request) {

	$selected_broker_ids = isset($_POST['broker_ids']) ? $_POST['broker_ids'] : array();
	$selected_city = isset($_POST['_buildout_city']) ? $_POST['_buildout_city'] : array();
	$selected_use = isset($_POST['_gsheet_use']) ? $_POST['_gsheet_use'] : array();
	$selected_neighbourhoods = isset($_POST['_gsheet_neighborhood']) ? $_POST['_gsheet_neighborhood'] : array();
	$selected_zip = isset($_POST['_gsheet_zip']) ? $_POST['_gsheet_zip'] : array();
	$selected_state = isset($_POST['_gsheet_state']) ? $_POST['_gsheet_state'] : array();
	$selected_vented = isset($_POST['_gsheet_vented']) ? $_POST['_gsheet_vented'] : array();
	$selected_type = isset($_POST['selected_type']) ? $_POST['selected_type'] : array();
	
	
  #_gsheet_listing_type input[type="checkbox"]:checked
	global $wpdb;
	$table_name = $wpdb->prefix . 'postmeta';
  $post_table = $wpdb->prefix . 'posts';
  $meta_key = '_gsheet_listing_agent';
  
  $query = "SELECT DISTINCT pm.meta_value 
			FROM $table_name AS pm 
			INNER JOIN $post_table AS p ON pm.post_id = p.ID 
			WHERE pm.meta_key = %s 
			AND p.post_status = 'publish' 
			AND p.post_type = 'properties'";
  
	// Parameters for the prepared statement
	$params = array($meta_key);
  /*   if (!empty($selected_broker_ids)) {
	  $query .= " AND post_id IN (
		  SELECT post_id 
		  FROM $table_name 
		  WHERE meta_key = '_gsheet_listing_agent' 
		  AND meta_value IN ('" . implode("','", $selected_zip) . "')
	  )";
	} */
	if (!empty($selected_zip)) {
	  $query .= " AND post_id IN (
		  SELECT post_id 
		  FROM $table_name 
		  WHERE meta_key = '_gsheet_zip' 
		  AND meta_value IN ('" . implode("','", $selected_zip) . "')
	  )";
	}
  
	if (!empty($selected_type)) {
	  $query .= " AND post_id IN (
		  SELECT post_id 
		  FROM $table_name 
		  WHERE meta_key = '_gsheet_listing_type' 
		  AND meta_value IN ('" . implode("','", $selected_type) . "')
	  )";
  }
  
	// Adding conditions for selected city if not empty
	if (!empty($selected_city)) {
		$query .= " AND post_id IN (
			SELECT post_id 
			FROM $table_name 
			WHERE meta_key = '_buildout_city' 
			AND meta_value IN ('" . implode("','", $selected_city) . "')
		)";
	}
  
	// Adding conditions for selected use if not empty
	if (!empty($selected_use)) {
		$query .= " AND post_id IN (
			SELECT post_id 
			FROM $table_name 
			WHERE meta_key = '_gsheet_use' 
			AND meta_value IN ('" . implode("','", $selected_use) . "')
		)";
	}
  
	// Adding conditions for selected neighbourhoods if not empty
	if (!empty($selected_neighbourhoods)) {
		$query .= " AND post_id IN (
			SELECT post_id 
			FROM $table_name 
			WHERE meta_key = '_gsheet_neighborhood' 
			AND meta_value IN ('" . implode("','", $selected_neighbourhoods) . "')
		)";
	}
  
	// Adding conditions for selected state if not empty
   /*  if (!empty($selected_state)) {
		$query .= " AND post_id IN (
			SELECT post_id 
			FROM $table_name 
			WHERE meta_key = '_gsheet_state' 
			AND meta_value IN ('" . implode("','", $selected_state) . "')
		)";
	} */
  
	if (!empty($selected_state)) {
	  $query .= " AND (post_id IN (
		  SELECT post_id 
		  FROM $table_name 
		  WHERE meta_key = '_gsheet_state' 
		  AND meta_value IN ('" . implode("','", $selected_state) . "')
	  ) OR post_id IN (
		  SELECT post_id 
		  FROM $table_name 
		  WHERE meta_key = '_buildout_state' 
		  AND meta_value IN ('" . implode("','", $selected_state) . "')
	  ))";
  }
  
	// Adding conditions for selected vented if not empty
	if (!empty($selected_vented)) {
		$query .= " AND post_id IN (
			SELECT post_id 
			FROM $table_name 
			WHERE meta_key = '_gsheet_vented' 
			AND meta_value IN ('" . implode("','", $selected_vented) . "')
		)";
	}
  
	// Preparing the query
	$query = $wpdb->prepare($query, $params);
  
	// Fetching results from the database
	$original_results = $wpdb->get_results($query);
  
	// Array to store the values obtained from the first query
	$matched_zip = array();
  
	// Storing the values obtained from the first query into the array
	if ($original_results) {
		foreach ($original_results as $result) {
			$matched_zip[] = $result->meta_value;
		}
	}
  
	// Fetching all results from the database
  //  $query_all = $wpdb->prepare("SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = %s", $meta_key);
  $query_all = $wpdb->prepare("
	  SELECT DISTINCT pm.meta_value 
	  FROM $table_name AS pm 
	  INNER JOIN {$wpdb->prefix}posts AS p ON pm.post_id = p.ID 
	  WHERE pm.meta_key = %s 
	  AND p.post_status = 'publish' 
	  AND p.post_type = 'properties'
  ", $meta_key);
	$results_all = $wpdb->get_results($query_all);
	$data = array();
  
	foreach ($results_all as $result) {
		$zip = $result->meta_value;
		$is_matched = in_array($zip, $matched_zip);
		$data[] = array(
			'id' => $zip,
			'text' => $zip,
			'matched' => $is_matched // Store if ZIP code is matched or not
		);
	}
  
	return $data;
  }
  