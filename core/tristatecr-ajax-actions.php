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
	if ($page_id) {
		$search_permalink = add_query_arg(['redirectId' => $page_id], $search_permalink);
	}



	$result = array(
		'search_id' 		=> $search_id,
		'recent_link'       => $search_permalink, //need to add permalink for view search
		'search_url' 		=> get_permalink($search_id),
		'map_name' 			=> get_the_title($search_id),
		'listing_ids' 	=> $listing_ids,
		'message' 			=> 'Sucessfully saved the results as a layer',
	);
	wp_send_json_success($result, 200);
}




/* ---------------savemap to layer pop up------------- */


add_action('wp_ajax_pop_tristate_save_results_as_layer', 'pop_tristate_save_results_as_layer');

function pop_tristate_save_results_as_layer()
{

	$savedSearchId 		= $_POST['savedSearchId'] ?? '';
	$getSearchId 		= $_POST['search_id'] ?? '';


	$get_search_id_final = !empty($savedSearchId) ? $savedSearchId : (!empty($getSearchId) ? $getSearchId : '');

	$get_current_user = get_current_user_id();

	$posts = triget_posts_by_author($get_current_user);
	// var_dump($posts);

	$select_options = '<option value="">Create a new map or select existing one</option>';

	if (is_array($posts)) {
		foreach ($posts as $post) {
			$selected = ($get_search_id_final == $post['ID']) ? 'selected' : '';
			$select_options .= '<option value="' . $post['ID'] . '" ' . $selected . '>' . $post['Title'] . '</option>';
		}
	} else {
		$select_options = '<option value="">' . $posts . '</option>'; // Display error message in the option
	}

	// Create the select element with the options
	$select_html = '<select name="previous_map_post_id" id="previous_map_post_id">' . $select_options . '</select>';

	// Output the select element
	//echo $select_html;


?>


	<?php

	$time = time();


	// Render the HTML if savedSearchId is not empty
	$render_map_title =
		'
			<li><label>Save to your existing Map!</label>
              ' . $select_html . '
          </li>
			
			<li id="map-title"><label>Map Title</label>
              <input type="text" name="map_post_title" id="map_post_title" required>
          </li>
		  ';


	$html = '<div class="tcr-popup-overlay"></div>

	<div class="tcr-popup-wrapper" id="tcr-popup-wrapper">

		<div class="tcr-popup-content" id="tcr-req-acc-output">
			
				<h4>SAVE TO A NEW MAP LAYER</h4>
				<form id="tri-popup-form" method="POST">
					<div id="map-layer-content">
						<ul>
							<input type="hidden" name="userid" id="map_layer_user_id" value="' . $get_current_user . '">
							<input type="hidden" name="timestamp" id="map_layer_timestamp" value="' . $time . '">
							' . $render_map_title . '


							<li>
								<label>Layer Title</label>
								<input type="text" name="map_layer_title" id="map_layer_title" required>
							</li>
						</ul>
						<div class="tcr-layer-footer">	
						<input type="hidden" name="map_layer_post_ids" id="map_layer_post_ids">
						<input type="submit" id="submit_map_layer" name="submit_layer" value="SAVE TO A NEW MAP LAYER">
						</div>

					</div>
				</form>
			
			<div id="map_layer_show_message"></div>
		</div>

		<button id="tcr-popup-close-button">X</button>
	</div>';

	echo $html;
	exit();
}


/* ----------------get author post-------------- */


function triget_posts_by_author($author_id)
{
	// Check if the author ID is valid
	if (!is_numeric($author_id)) {
		return 'Invalid author ID';
	}

	// Arguments for the query
	$args = array(
		'author' => $author_id,   // Specify the author ID
		'posts_per_page' => -1,
		'post_type' =>  'properties_search'  // Retrieve all posts by this author
	);

	// Custom query
	$query = new WP_Query($args);

	// Initialize an array to hold post IDs and titles
	$posts_list = array();

	// Check if there are any posts
	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			$post_id = get_the_ID();
			$post_title = get_the_title();
			// Add the post ID and title to the array
			$posts_list[] = array('ID' => $post_id, 'Title' => $post_title);
		}
	} else {
		return 'No posts found for the author.';
	}

	// Reset Post Data
	wp_reset_postdata();

	// Return the array of post IDs and titles
	return $posts_list;
}

// Example usage: Call the function with the desired author ID
// $author_id = 123; // Replace 123 with the actual author ID
// $posts = triget_posts_by_author($author_id);

// // Display the posts
// if (is_array($posts)) {
// 	foreach ($posts as $post) {
// 		echo 'Post ID: ' . $post['ID'] . ', Title: ' . $post['Title'] . '<br>';
// 	}
// } else {
// 	echo $posts; // Display error message
// }


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
