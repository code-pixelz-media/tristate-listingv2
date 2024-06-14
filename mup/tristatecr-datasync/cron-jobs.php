<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Schedule our cron actions
add_filter( 'cron_schedules', 'tristatecr_datasync_cron_schedules' );

function tristatecr_datasync_cron_schedules( $schedules ) {
	$schedules['every_15_minutes'] = array(
		'interval' => 900,
		'display' => __( 'Every 15 minutes' ),
	);
	return $schedules;
}

// Schedule an action if it's not already scheduled
if ( ! wp_next_scheduled( 'tristatecr_datasync_cron' ) ) {
	wp_schedule_event( time(), 'every_15_minutes', 'tristatecr_datasync_cron' );
}

// Hook into that action that'll fire every 15 minutes
add_action( 'tristatecr_datasync_cron', 'tristatecr_datasync_cron_function' );

function tristatecr_datasync_cron_function() {
	update_option( 'tristatecr_datasync_cron_last_started', time() );

	$time = time();
	$datetime = date('Y-m-d H:i:ss', $time);

	$message = "Running tristatecr_datasync_cron_function at $datetime\n";
	error_log($message, 3, LOG_FILE);

	$args = array();
	$aargs = array(
		'skip' => 'remote',
	);
	$result = tristatectr_datasync_command( $args, $aargs );
	$message = "\nResults: " . print_r($result, 1) . "\n";
	error_log($message, 3, LOG_FILE);
}