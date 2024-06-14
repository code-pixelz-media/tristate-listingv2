<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if the nonce matches
function tristatecr_has_private_data_access() {
	return is_user_logged_in();
	if ( !isset($_REQUEST['wp_rest']) ) {
		return;
	}
	if ( !wp_verify_nonce( $_REQUEST['wp_rest'], 'wp_rest' ) ) {
		return;
	} else {
		return true;
	}
}

// Register WP REST API routes
add_action( 'rest_api_init', 'tristatectr_rest_api_init' );

function tristatectr_rest_api_init() {
	register_rest_route( 'tristatectr/v1', '/listings', array(
		'methods' => 'GET',
		'callback' => 'tristatectr_rest_listings',
	) );
	register_rest_route( 'tristatectr/v1', '/brokers', array(
		'methods' => 'GET',
		'callback' => 'tristatectr_rest_brokers',
	) );
	register_rest_route( 'tristatectr/v2', '/brokers', array(
		'methods' => 'GET',
		'callback' => 'tristatectr_rest_brokers_v2',
	) );
}

function tristatectr_rest_listings( $data ) {
	$posts = get_posts( array(
		'post_type' => 'tsc_property',
		'posts_per_page' => -1,
	) );

	if ( empty( $posts ) ) {
		return null;
	}

	$results = array();
	foreach ( $posts as $post ) {

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
		$buildout_id 			= (int) get_post_meta( $ID, '_buildout_id', true );
		$title 						= get_post_meta( $ID, '_buildout_sale_listing_web_title', true );
		$subtitle 				= implode(', ', array( get_post_meta( $ID, '_buildout_city', true ), get_post_meta( $ID, '_buildout_county', true ), get_post_meta( $ID, '_buildout_state', true ) ));
		$badges 					= array(
			'use' 				=> get_post_meta( $ID, '_gsheet_use', true ),
			'type' 				=> get_post_meta( $ID, '_gsheet_listing_type', true ),
			'price_sf' 		=> get_post_meta( $ID, '_gsheet_price_sf', true ),
			'commission' 	=> get_post_meta( $ID, '_gsheet_commission', true ) 
		);
		$_use 						= get_post_meta( $ID, '_gsheet_use', true );
		$_type 						= get_post_meta( $ID, '_gsheet_listing_type', true );
		$_price_sf 				= get_post_meta( $ID, '_gsheet_price_sf', true );
		$_price_sf 				= preg_replace( '/\.[0-9]+/', '', $_price_sf );
		$_price_sf 				= (int) preg_replace( '/[^0-9]/', '', $_price_sf );
		$_commission 			= get_post_meta( $ID, '_gsheet_commission', true );
		$summary 					= get_post_meta( $ID, '_buildout_location_description', true );
		$min_size 				= get_post_meta( $ID, '_gsheet_min_size', true );
		$max_size 				= get_post_meta( $ID, '_gsheet_max_size', true );
		$size 						= $min_size ?? $max_size;
		$size 						= preg_replace( '/\.[0-9]+/', '', $size );
		$size 						= (int) preg_replace( '/[^0-9]/', '', $size );
		$zoning 					= get_post_meta( $ID, '_buildout_zoning', true );
		$key_tag 					= get_post_meta( $ID, '_gsheet_key_tag', true );
		$agents 					= (array) tristatectr_get_brokers_with_excluded( get_post_meta($ID, '_buildout_broker_ids', true) );
		$_agent 					= get_post_meta( $ID, '_gsheet_listing_agent', true );
		$lease_out 				= get_post_meta( $ID, '_gsheet_lease_out', true );

		$lease_conditions = get_post_meta( $ID, '_buildout_lease_description', true );
		$lease_conditions = get_post_meta( $ID, '_gsheet_lease_conditions', true );

		$bo_price 				= get_post_meta( $ID, '_buildout_sale_price_dollars', true );
		$price 						= get_post_meta( $ID, '_gsheet_monthly_rent', true );
		// Remove fractional units from the price
		$_price 					= preg_replace( '/\.[0-9]+/', '', $price );
		// Convert the price to integer value
		$_price = (int) preg_replace( '/[^0-9]/', '', $_price );
		$more_info 				= get_post_meta( $ID, '_gsheet_link_to_more_info', true );
		$more_info 				= get_post_meta( $ID, '_buildout_sale_listing_url', true ) ?? get_post_meta( $ID, '_buildout_lease_listing_url', true );
		$tour3d 					= get_post_meta( $ID, '_gsheet_3d_tour', true );
		$tour3d 					= get_post_meta( $ID, '_buildout_matterport_url', true );
		$youtube_url 			= get_post_meta( $ID, '_buildout_you_tube_url', true );
		$zip 							= get_post_meta( $ID, '_gsheet_zip', true ) ?? get_post_meta( $ID, '_buildout_zip', true );
		$neighborhood 		= get_post_meta( $ID, '_gsheet_neighborhood', true );
		$vented 					= get_post_meta( $ID, '_gsheet_vented', true );
		$city 						= get_post_meta( $ID, '_buildout_city', true );
		$borough 					= get_post_meta( $ID, '_gsheet_borough', true );
		$state 						= get_post_meta( $ID, '_gsheet_state', true );

		$image 						= false;
		if ( $photos = get_post_meta( $ID, '_buildout_photos', true ) ) {
			$photo = reset($photos);
			$image = $photo->formats->thumb ?? '';
		}

		$lat 							= get_post_meta( $ID, '_buildout_latitude', true );
		$lng 							= get_post_meta( $ID, '_buildout_longitude', true );

		$buildout_notes 	= get_post_meta( $ID, '_buildout_notes', true );
		$gsheet_notes 		= get_post_meta( $ID, '_gsheet_notes', true );

		$buildout_synced 	= get_post_meta( $ID, '_buildout_last_updated', true ) ?? false;
		$sheets_synced 		= get_post_meta( $ID, '_gsheet_last_updated', true ) ?? false;

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
		);
	}

	return $results;
}

function tristatectr_rest_brokers() {
	$brokers = get_option('tristatecr_datasync_brokers') ?? array();
	return (array) $brokers;
}

function tristatectr_rest_brokers_v2() {
	$brokers = get_option('tristatecr_datasync_brokers') ?? array();
	$results = array();
	foreach( $brokers as $key => $value ) {
		$results[] = array(
			'id' => $key,
			'name' => $value,
		);
	}
	return (array) $results;
}