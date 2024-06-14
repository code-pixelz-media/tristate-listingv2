<?php
/**
 * Helper functions.
 */

function np_log( $message, $data = null ) {
	if ( !is_null($data) ) $message . ' ' . print_r($data, 1);

	if (defined('WP_CLI')) {
		WP_CLI::log( $message );
	} else {
		error_log( $message, 3, LOG_FILE );
	}
}

function tristatectr_get_brokers_with_excluded( $broker_ids = array() ) {
	$brokers = (array) get_option('tristatecr_datasync_brokers');
	$excluded_brokers = array('Shlomi Bagdadi');

	$array = array();
	foreach( $broker_ids as $broker_id ) {
		$array[$broker_id] = $brokers[ $broker_id ] ?? $broker_id;
	}
	if ( count($array) > 1 ) {
		$array = array_diff( $array, $excluded_brokers );
	}
	return array_filter( $array );
}