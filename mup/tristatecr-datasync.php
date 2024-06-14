<?php
/**
 * Plugin Name: Tristate CR Data Sync
 */

if (!defined('ABSPATH')) {
	die();
}

define( 'TRISTATECR_DATASYNC_VERSION', '0.1.0' );
define( 'TRISTATECR_DATASYNC_PATH', dirname(__FILE__) );
define( 'LOG_FILE', dirname(__FILE__) . '/datasync.log' );

define('CRON_STATUS_OPTION', 'tristatecr_datasync_cron_last_status');
define('CRON_LAST_RESULT_OPTION', 'tristatecr_datasync_cron_last_result');

require_once( dirname(__FILE__) . '/tristatecr-datasync/helpers.php' );

require_once( dirname(__FILE__) . '/tristatecr-datasync/content-types.php' );
require_once( dirname(__FILE__) . '/tristatecr-datasync/cron-jobs.php' );

require_once( dirname(__FILE__) . '/tristatecr-datasync/api-buildout.php' );
require_once( dirname(__FILE__) . '/tristatecr-datasync/api-google-csv.php' );
require_once( dirname(__FILE__) . '/tristatecr-datasync/cli-commands.php' );

require_once( dirname(__FILE__) . '/tristatecr-datasync/rest-api.php' );
require_once( dirname(__FILE__) . '/tristatecr-datasync/ajax-actions.php' );

require_once( dirname(__FILE__) . '/tristatecr-datasync/options-page.php' );