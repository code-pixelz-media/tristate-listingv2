<?php

// new_tristate_save_results_as_layer ajax action saves the data as a 
// database option and retuns a message to the client

add_action('wp_ajax_new_tristate_save_results_as_layer', 'new_tristate_save_results_as_layer');

function new_tristate_save_results_as_layer()
{
	//$nonce = $_POST['nonce'];
	// if (!wp_verify_nonce($nonce, 'new_tristate_save_results_as_layer')) {
	// 	return wp_send_json_error(array(
	// 		'message' => 'Invalid nonce',
	// 	), 403);
	// }

	// if (!current_user_can('edit_posts')) {
	// 	return wp_send_json_error(array(
	// 		'message' => 'You do not have permission to save results as a layer',
	// 	), 403);
	// }

	$search_id 		= $_POST['search_id'] ?? '';
	$user_id 			= $_POST['user_id'] ?? get_current_user_id();
	$timestamp 		= $_POST['timestamp'] ?? time();
	$listing_ids 	= $_POST['listing_ids'] ?? '';
	// $listing_ids 	= explode(',', $listing_ids);
	// $listing_ids 	= array_map('intval', $listing_ids);
	$layer_name 	= $_POST['layer_name'] ?? '';
	$map_name 	= $_POST['get_map_title'] ?? '';
	$layer_name 	= sanitize_text_field($layer_name);
	$map_name_title 	= sanitize_text_field($map_name);
	$page_id = $_POST['page_id'];
	// Create a post if the search_id is empty
	if (empty($search_id)) {
		$post_name 	= substr(md5('search-' . $timestamp), 0, 12);
		$post_title = isset($map_name_title) ? $map_name_title : 'Search @' . $post_name;
		$post_id = wp_insert_post(array(
			'post_type' 		=> 'properties_search',
			'post_title' 		=> $post_title,
			'post_status' 	=> 'publish',
			'post_author' 	=> $user_id,
			'post_name' 		=> $post_name,
		));
		$search_id = $post_id;
	}


	// Save the listings as a post meta
	// $listing_ids = array_map('intval', $listing_ids);
	// $listing_ids = array_filter($listing_ids);
	// $listing_ids = implode(',', $listing_ids);
	$result = add_post_meta($search_id, 'listing_ids', $listing_ids);
	$result = add_post_meta($search_id, 'layer_name', $layer_name);
	$search_permalink =  get_the_permalink($search_id);
	if($page_id){
		$search_permalink = add_query_arg(['redirectId' => $page_id], $search_permalink);
	}

	

	$result = array(
		'search_id' 		=> $search_id,
		'recent_link'       =>$search_permalink, //need to add permalink for view search
		'search_url' 		=> get_permalink($search_id),
		'map_name' 			=> get_the_title($search_id),
		'listing_ids' 	=> $listing_ids,
		'message' 			=> 'Sucessfully saved the results as a layer',
	);
	wp_send_json_success($result, 200);
}

// add_action('wp_ajax_rename_search', 'tristatecr_wp_ajax_rename_search');

// function tristatecr_wp_ajax_rename_search()
// {
// 	$nonce = $_POST['nonce'];
// 	if (!wp_verify_nonce($nonce, 'dashboard_actions')) {
// 		return wp_send_json_error(array(
// 			'message' => 'Invalid nonce',
// 		), 403);
// 	}

// 	if (!current_user_can('edit_posts')) {
// 		return wp_send_json_error(array(
// 			'message' => 'You do not have permission to rename this search',
// 		), 403);
// 	}

// 	$post_id = $_POST['post_id'] ?? '';
// 	$post_id = intval($post_id);

// 	$new_name = $_POST['new_name'] ?? '';
// 	$new_name = sanitize_text_field($new_name);

// 	$result = wp_update_post(array(
// 		'ID' 					=> $post_id,
// 		'post_title' 	=> $new_name,
// 	));

// 	if ($result) {
// 		wp_send_json_success(array(
// 			'message' => 'Successfully renamed the search',
// 		), 200);
// 	} else {
// 		wp_send_json_error(array(
// 			'message' => 'Failed to rename the search',
// 		), 500);
// 	}
// }

// add_action('wp_ajax_delete_search', 'tristatecr_wp_ajax_delete_search');

// function tristatecr_wp_ajax_delete_search()
// {
// 	$nonce = $_POST['nonce'];
// 	if (!wp_verify_nonce($nonce, 'dashboard_actions')) {
// 		return wp_send_json_error(array(
// 			'message' => 'Invalid nonce',
// 		), 403);
// 	}

// 	if (!current_user_can('edit_posts')) {
// 		return wp_send_json_error(array(
// 			'message' => 'You do not have permission to delete this search',
// 		), 403);
// 	}

// 	$post_id = $_POST['post_id'] ?? '';
// 	$post_id = intval($post_id);

// 	$result = wp_delete_post($post_id, true);

// 	if ($result) {
// 		wp_send_json_success(array(
// 			'message' => 'Successfully deleted the search',
// 		), 200);
// 	} else {
// 		wp_send_json_error(array(
// 			'message' => 'Failed to delete the search',
// 		), 500);
// 	}
// }
