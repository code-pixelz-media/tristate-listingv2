<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Register a simple options page

add_action( 'admin_menu', 'tristatecr_datasync_options_page' );

function tristatecr_datasync_options_page() {
	add_options_page(
		'Tristate CR Data Sync',
		'Tristate CR Data Sync',
		'manage_options',
		'tristatecr-datasync',
		'tristatecr_datasync_options_page_html'
	);
}

function tristatecr_datasync_options_page_html() {
	// check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// add error/update messages

	// check if the user have submitted the settings
	// wordpress will add the "settings-updated" $_GET parameter to the url
	if ( isset( $_GET['settings-updated'] ) ) {

		// add settings saved message with the class of "updated"
		add_settings_error( 'tristatecr_datasync_messages', 'tristatecr_datasync_message', __( 'Settings Saved', 'tristatecr-datasync' ), 'updated' );
	}

	// show error/update messages
	settings_errors( 'tristatecr_datasync_messages' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			// output security fields for the registered setting "tristatecr_datasync"
			settings_fields( 'tristatecr_datasync' );
			// output setting sections and their fields
			// (sections are registered for "tristatecr_datasync", each field is registered to a specific section)
			do_settings_sections( 'tristatecr_datasync' );

			// Show a log of the last cron run
			$last_started = get_option( 'tristatecr_datasync_cron_last_started' );
			$last_started = $last_started ? date( 'Y-m-d H:i:s', $last_started ) : 'Never';
			$last_status = get_option( CRON_STATUS_OPTION );

			?>
			<h2>Last Cron Run</h2>
			<p>
				Started: <?php echo $last_started; ?><br>
				Status: <?php echo $last_status; ?><br>
				<pre><?php echo print_r( get_option( CRON_LAST_RESULT_OPTION ) ); ?></pre>
			</p>
			<?php

			// output save settings button
			submit_button( 'Save Settings' );
			?>
		</form>
	</div>
	<?php
}