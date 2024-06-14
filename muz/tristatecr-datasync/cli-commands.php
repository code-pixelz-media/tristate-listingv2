<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Imports and syncs Buildout and Google Sheets data.
 */
function tristatectr_datasync_command( $args, $aargs = array() ) {

	defined('DOING_CRON') && update_option( CRON_STATUS_OPTION, 'Starting' );

	global $wpdb;
	$start = microtime(true);

	// Arguments
	$skip = explode( ',', $aargs['skip'] ?? '' ) ?? array();
	$message = 'Skipping: ' . implode( ', ', $skip );
	$force_update = in_array('force-update', $aargs);
	defined('WP_CLI') && WP_CLI::debug($message);

	// Imported data
	$results = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = '_import_buildout_id'" );
	$imported_ids = wp_list_pluck( $results, 'meta_value', 'post_id' );

	$results = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = '_import_buildout_checksum'" );
	$buildout_checksums = wp_list_pluck( $results, 'meta_value', 'post_id' );

	$results = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = '_import_gsheet_checksum'" );
	$sheets_checksums = wp_list_pluck( $results, 'meta_value', 'post_id' );

	// Counters
	$counter = array(
		// buildout
		'errors' 		=> 0,
		'updated' 	=> 0,
		'imported' 	=> 0,
		'skipped' 	=> 0,
		// sheets
		'found' 		=> 0,
		'missing' 	=> 0,
		'matched' 	=> 0,
	);

	// Load the Brokers
	$message = "\nReading Brokers...";
	if (array_intersect(['json'], $skip)) $message .= ' Skipping';
	np_log($message);
	
if (!array_intersect(['json'], $skip)) :
	defined('DOING_CRON') && update_option( CRON_STATUS_OPTION, 'Reading Brokers' );
	$fn = 'https://buildout.com/api/v1/1f4213e0ac96dea2fde27fa0ba30547c8858889b/brokers.json?limit=999';
	$contents = file_get_contents( $fn );
	$data 		= json_decode( $contents, false );
	$brokers_checksum = md5($contents);

	if ( $brokers_checksum != get_option('tristatecr_datasync_brokers_checksum') ) {
		defined('DOING_CRON') && update_option( CRON_STATUS_OPTION, 'Updating Brokers' );
		$message = "Brokers checksum changed. Updating...";
		np_log($message);

		$brokers = array();
		foreach ((array) $data->brokers as $item) {
			$broker_id = $item->id;
			$broker_fullname = implode(' ', array($item->first_name, $item->last_name));
			$brokers[$broker_id] = $broker_fullname;
		}

		update_option('tristatecr_datasync_brokers_checksum', $brokers_checksum);
		update_option('tristatecr_datasync_brokers', $brokers);
	} else {
		defined('DOING_CRON') && update_option( CRON_STATUS_OPTION, 'Brokers unchanged' );
		$message = "Brokers checksum unchanged. Skipping...";
		$brokers = get_option('tristatecr_datasync_brokers');
		np_log($message);
	}
endif;

	// Buildout JSON
	$message = "\nReading Buildout JSON...";
	if (array_intersect(['json'], $skip)) $message .= ' Skipping';
	np_log($message);
	$filenames = array(
		'https://buildout.com/api/v1/1f4213e0ac96dea2fde27fa0ba30547c8858889b/properties.json?limit=999',
	);
	if (!array_intersect(['json'], $skip))
	foreach( $filenames as $fn ) {
		defined('DOING_CRON') && update_option( CRON_STATUS_OPTION, 'Reading Buildout JSON' );
		$context 	= stream_context_create( ['http' => ['ignore_errors' => false]] );
		$contents = file_get_contents( $fn, false, $context );
		$data 	= json_decode( $contents, false );
		$count 	= count( $data->properties ?? array() );
		
		$message = 'Found ' . $count . ' records in ' . $fn . '.';
		np_log($message);

		foreach( $data->properties as $item ) {
			$id 	= np_generate_buildout_item_id($item);
			$name = $item->name;
			$checksum = md5( json_encode( $item ) );
			$message = "Processing #$id: \"$name\"";
			defined('WP_CLI') && WP_CLI::log($message);
			
			$postarr = np_process_buildout_item( $item );

			$post_id = false;

			if ( $found_id = array_search($id, $imported_ids) ) {
				// UPDATE
				$message = '-- Existing post ID ' . $found_id . ' for ' . $id . '.';
				defined('WP_CLI') && WP_CLI::log($message);
				$post_id = $postarr['ID'] = $found_id;

				$existing_checksum = $buildout_checksums[$found_id] ?? '';
				if ( !$force_update && ($existing_checksum == $checksum) )  {
					$message = "--- No changes detected, checksum $checksum matches";
					defined('WP_CLI') && WP_CLI::log($message);
					$message = "--- Skipping.";
					defined('WP_CLI') && WP_CLI::log($message);
					$counter['skipped']++;
					continue;
				} else {
					$message = "--- Changes detected, checksum $checksum does not match $existing_checksum";
					defined('WP_CLI') && WP_CLI::log($message);
				}

				$result = wp_update_post( $postarr );
				if ( is_wp_error( $result ) ) {
					$message = $result->get_error_message();
					defined('WP_CLI') && WP_CLI::error($message);
					$counter['errors']++;
				} else {
					$message = '--- Updated existing post ID ' . $result;
					defined('WP_CLI') && WP_CLI::log($message);
					$counter['updated']++;
				}
			} else {
				// CREATE
				$message = '-- Creating new post for ' . $id . '.';
				defined('WP_CLI') && WP_CLI::log($message);
				$result = wp_insert_post( $postarr );
				if ( is_wp_error( $result ) ) {
					$message = '--- Created new post ID ' . $result;
					defined('WP_CLI') && WP_CLI::error($message);
					$counter['errors']++;
				} else {
					$post_id = $result;
					$message = '--- Created new post ID ' . $result;
					defined('WP_CLI') && WP_CLI::log($message);
					$counter['imported']++;
					$imported_ids[$post_id] = $id;
				}
			}

			if ( $post_id ) {
				update_post_meta( $post_id, '_buildout_last_updated', time() );
			}
		}
	}

	// Sheets CSV
	$message = "\nReading Sheets CSV...";
	if (in_array('csv', $skip)) $message .= ' Skipping';
	np_log($message);
	if (!in_array('csv', $skip)) 
	foreach( $filenames as $fn ) {
		defined('DOING_CRON') && update_option( CRON_STATUS_OPTION, 'Reading Sheets CSV' );
		defined('WP_CLI') && WP_CLI::log('Reading ' . $fn . '...');
		if (($handle = fopen($fn, "r")) !== FALSE) {
			$row = 0;
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				$row++;

				// Header row
				if ($row == 1) {
					$header = $data;
					array_walk($header, function(&$item) {
						$item = sanitize_title( $item );
						$item = strtolower( str_replace('-', '_', $item) );
					});
					continue;
				}

				// Data row
				$item 		= (object) array_combine($header, $data);
				$id 			= np_generate_google_csv_item_id($item);
				$checksum = md5( json_encode( $item ) );
				$message = "- Processing #$id";
				defined('WP_CLI') && WP_CLI::log($message);

				if ( $buildout_id = $item->buildout_id ?? false ) {
					$message = "-- Found Buildout ID $buildout_id for row";
					defined('WP_CLI') && WP_CLI::log($message);
				} else {
					$message = "-- No Buildout ID found for row";
					defined('WP_CLI') && WP_CLI::log($message);
					$counter['missing']++;
					continue;
				}

				// Find the imported post_id
				$post_id = array_search($buildout_id, $imported_ids);
				if ( !$post_id ) {
					$message = "-- No post ID found";
					defined('WP_CLI') && WP_CLI::log($message);
					$counter['missing']++;
					continue;
				} else {
					$message = "-- Found post ID $post_id";
					defined('WP_CLI') && WP_CLI::log($message);
					$counter['found']++;
				}

				// Check the checksum
				$post_sheet_checksum = $sheets_checksums[$post_id] ?? false;
				
				if ( !$force_update && ($post_sheet_checksum && $post_sheet_checksum == $checksum) )  {
					$message = "--- No changes detected, checksum $checksum matches.";
					defined('WP_CLI') && WP_CLI::log($message);
					$message = "--- Skipping.";
					defined('WP_CLI') && WP_CLI::log($message);
					continue;
				} else {
					$message = "--- Changes detected, checksum $checksum does not match $post_sheet_checksum";
					defined('WP_CLI') && WP_CLI::log($message);
				}

				$sheet_meta = np_process_google_csv_item_meta( $item );

				// Update the post meta
				$message = "--- Updating post_meta for post_id:$post_id buildout_id:$buildout_id";
				defined('WP_CLI') && WP_CLI::log($message);

				foreach( $sheet_meta as $key => $value ) {
					$message = "---- Updating $key to $value";
					defined('WP_CLI') && WP_CLI::log($message);
					update_post_meta( $post_id, $key, $value );
					update_post_meta( $post_id, '_gsheet_last_updated', time() );
				}

				$counter['matched']++;

			}
			fclose($handle);
		}

		$message = 'Found ' . $row . ' records in ' . $fn . '.';
		np_log($message);
	}

	$message = "Counters: " . print_r($counter, true);
	defined('WP_CLI') && WP_CLI::log($message);

	defined('DOING_CRON') && update_option( CRON_STATUS_OPTION, 'Done' );
	defined('DOING_CRON') && update_option( CRON_LAST_RESULT_OPTION, $counter );

	$secs = microtime(true) - $start;
	$message = "Done in $secs seconds.";
	np_log($message);

	return $counter;

}
defined('WP_CLI') && WP_CLI::add_command( 'datasync', 'tristatectr_datasync_command' );