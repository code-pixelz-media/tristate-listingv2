<?php
/**
 * Google Sheets API helpers.
 */

function np_process_google_csv_item( $data = null ) {
	$result = array();
	$result['post_title'] 	= $data->address ?? '';
	$result['post_content'] = $data->notes ?? '';
	$result['post_status'] 	= 'publish';
	$result['post_type'] 		= 'tsc_property';

	$result['tax_input'] 		= array();
	$result['meta_input'] 	= np_process_google_csv_item_meta( $data );
	
	// defined('WP_CLI') && WP_CLI::log(print_r($data, 1));
	return $result;
}

function np_process_google_csv_item_meta( $data = null ) {
	$checksum = md5( json_encode( $data ) );
	$result = array(
		'_import_from' 						=> 'sheets',
		'_import_gsheet_checksum' => $checksum,
	);

	$props = array_keys(get_object_vars($data));

	foreach( $props as $prop ) {
		$key = '_gsheet_' . $prop;
		$result[ $key ] = $data->$prop ?? '';
	}

	return array_filter($result);
}

function np_generate_google_csv_item_id( $data = null ) {
	$fields = array(
		$data->address ?? '',
		$data->cross_street ?? '',
		$data->neighborhood ?? '',
		$data->key_tag ?? '',
	);
	return sanitize_title( implode( '-', $fields ) );
}