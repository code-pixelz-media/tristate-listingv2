<?php
if (!defined('ABSPATH')) {
    exit;
}

define('NEW_CRON_STATUS_OPTION', 'tristate_cron_status');

define('NEW_CRON_LAST_RESULT_OPTION', 'tristate_cron_last_result');
define('NEW_LOG_FILE', TRISTATECRLISTING_PLUGIN_DIR . 'debug.log');
/**
 * Imports and syncs Buildout and Google Sheets data.
 */
function tristatectr_datasync_command_v2($args, $aargs = array())
{

    defined('DOING_CRON') && update_option(NEW_CRON_STATUS_OPTION, 'Starting');

    global $wpdb;
    $start = microtime(true);

    $settings = get_option('tristate_cr_settings');
    $get_buildout_api_key = $settings['buildout_api_key'];

    // Arguments
    $skip = explode(',', $aargs['skip'] ?? '') ?? array();
    $message = 'Skipping: ' . implode(', ', $skip);
    $force_update = in_array('force-update', $aargs);
    defined('WP_CLI') && WP_CLI::debug($message);

    // Imported data
    $results = $wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = '_import_buildout_id'");
    $imported_ids = wp_list_pluck($results, 'meta_value', 'post_id');

    $results = $wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = '_import_buildout_checksum'");
    $buildout_checksums = wp_list_pluck($results, 'meta_value', 'post_id');

    $results = $wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = '_import_gsheet_checksum'");
    $sheets_checksums = wp_list_pluck($results, 'meta_value', 'post_id');

    // Counters
    $counter = array(
        // buildout
        'errors'         => 0,
        'updated'     => 0,
        'imported'     => 0,
        'skipped'     => 0,
        // sheets
        'found'         => 0,
        'missing'     => 0,
        'matched'     => 0,
    );

/********************************--Brokers Sync Start--*****************************************/

    $message = "\nReading Brokers...";
    if (array_intersect(['json'], $skip)) $message .= ' Skipping';
    NEW_np_log($message);

    if (!array_intersect(['json'], $skip)) :
        defined('DOING_CRON') && update_option(NEW_CRON_STATUS_OPTION, 'Reading Brokers');
        $fn = 'https://buildout.com/api/v1/' . $get_buildout_api_key . '/brokers.json?limit=999';
        $contents = file_get_contents($fn);
        // $data         = json_decode($contents, false);
        $brokers_data = json_decode($contents, true);
        $brokers_checksum = md5($contents);

        if ($brokers_checksum != get_option('tristatecr_datasync_brokers_checksum')) {
            defined('DOING_CRON') && update_option(NEW_CRON_STATUS_OPTION, 'Updating Brokers');
            $message = "Brokers checksum changed. Updating...";
            NEW_np_log($message);

            // $brokers = array();
            // foreach ((array) $data->brokers as $item) {
            //     $broker_id = $item->id;
            //     $broker_fullname = implode(' ', array($item->first_name, $item->last_name));
            //     $brokers[$broker_id] = $broker_fullname;
            // }


            /*  ----------Start New broker Data sync -------------- */
            //$brokers_data = $data;
            $brokers_array = array(); // Initialize array to store broker data

            // Iterate over each broker
            foreach ($brokers_data['brokers'] as $broker) {
                // Extract required broker data
                $id = $broker['id'];
                $first_name = $broker['first_name'];
                $last_name = $broker['last_name'];
                $phone_number = $broker['phone_number'];
                $email = $broker['email'];
                $address = $broker['address'] . ", " . $broker['city'] . ", " . $broker['state'] . " " . $broker['zip'];
                $job_title = $broker['job_title'];
                $profile_photo_url = $broker['profile_photo_url'];

                // Store broker data in array
                $broker_info = array(
                    'id' => $id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'phone_number' => $phone_number,
                    'email' => $email,
                    'address' => $address,
                    'job_title' => $job_title,
                    'profile_photo_url' => $profile_photo_url,
                );

                // Add broker info to brokers array
                $brokers_array[] = $broker_info;
            }

            /*  ----------End New broker Data sync -------------- */

            update_option('tristatecr_datasync_brokers_checksum', $brokers_checksum);
            update_option('tristatecr_datasync_brokers', $brokers_array);
        } else {
            defined('DOING_CRON') && update_option(NEW_CRON_STATUS_OPTION, 'Brokers unchanged');
            $message = "Brokers checksum unchanged. Skipping...";
            $brokers = get_option('tristatecr_datasync_brokers');
            NEW_np_log($message);
        }
    endif;
/********************************--Brokers Sync Ends--*****************************************/


/********************************--Lease Space Sync--*****************************************/
 $message = "\nReading LeaseSpace JSON...";
 if (array_intersect(['json'], $skip)) $message .= ' Skipping';
 NEW_np_log($message);
 if (!array_intersect(['json'], $skip)){
   
     if (defined('TRI_STATE_SYNC_LEASE_SPACE_RUNNING') && TRI_STATE_SYNC_LEASE_SPACE_RUNNING) {
         return;
     }
     define('TRI_STATE_SYNC_LEASE_SPACE_RUNNING', true);
     $offset = 0;
     $limit = 100;  
     $all_lease_spaces = [];
     $new_checksum = '';
     $max_retries = 10;  
     $timeout = 20;  
 
     NEW_np_log("Starting data synchronization process for lease spaces...\n");
 
     // Record the start time for the whole process
     $total_start_time = microtime(true);
 
     // Fetch data in chunks using offset and limit
     while (true) {
         $attempt = 0;
         $success = false;
         $fetch_start_time = microtime(true);
 
         while ($attempt < $max_retries && !$success) {
             $response = wp_remote_get('https://buildout.com/api/v1/' . $get_buildout_api_key . '/lease_spaces.json?limit=' . $limit . '&offset=' . $offset, array(
                 'headers' => array(
                     'Accept' => 'application/json',
                 ),
                 'timeout' => $timeout  
             ));
 
             if (is_wp_error($response)) {
                 NEW_np_log('Buildout API request failed on attempt ' . ($attempt + 1), $response->get_error_message());
                 $attempt++;
                 if ($attempt >= $max_retries) {
                     NEW_np_log("Max retries reached. Exiting the synchronization process.\n");
                     return;  
                 }
             } else {
                 $success = true;
             }
         }
 
         if (200 !== wp_remote_retrieve_response_code($response)) {
             NEW_np_log('Unexpected response code: ' . wp_remote_retrieve_response_code($response) . "\n");
             break;  // Exit if the response is not successful
         }
 
         $lease_data = json_decode(wp_remote_retrieve_body($response));
         $lease_spaces = $lease_data->lease_spaces;
         
         if (empty($lease_spaces)) {
             break; 
         }
 
         // Accumulate fetched data
         $all_lease_spaces = array_merge($all_lease_spaces, $lease_spaces);
         // Increment the offset by the limit
         $offset += $limit;
         // Record the end time for each fetch and calculate the elapsed time
         $fetch_end_time = microtime(true);
         
         $fetch_time = $fetch_end_time - $fetch_start_time;
 
         // Logging for debugging
         NEW_np_log('Fetched ' . count($lease_spaces) . ' records in ' . $fetch_time . ' seconds. Total so far: ' . count($all_lease_spaces) . "\n");
     }
 
     // Record the end time for the whole process and calculate the elapsed time
     $total_end_time = microtime(true);
     $total_time = $total_end_time - $total_start_time;
     NEW_np_log('Total time required to fetch all data: ' . $total_time . ' seconds' . "\n");
 
     // Calculate checksum for the new data
     $new_checksum = md5(json_encode($all_lease_spaces));
     NEW_np_log('New checksum: ' . $new_checksum . "\n");
 
     // Compare with the stored checksum
     if ($new_checksum != get_option('tristatecr_datasync_lease_checksum')) {
         NEW_np_log('Checksum Changed..Inserting...' . $new_checksum . "\n");
         global $wpdb;
         $space_tbl = $wpdb->prefix . 'lease_spaces';
         $filtered_lease_spaces = array_filter($all_lease_spaces, function($space) {
             return $space->deal_status_id == 1;
         });
         $extracted_data = array_map(function($space) {
             $values = [
                 $space->id,//0
                 $space->property_id,//1
                 $space->lease_title ?? 'false',
                 $space->lease_rate_units,//3
                 $space->lease_rate,//4
                 $space->space_size_units,//5
                 $space->size_sf,//6
                 $space->floor,//7
                 $space->deal_status_id,//8
                 $space->space_type_id,//9
                 $space->address2 ?? 'false',//10
                 $space->suite,//11
                 $space->description,//12
                 $space->lease_type_id,//13
                 
             ];

             return [
                 'lease_id' => $values[0],//1
                 'property_id' => $values[1],//2
                 'lease_title' => $values[2],//3
                 'lease_rate_units' => $values[3],//4
                 'lease_rate' => $values[4],//5
                 'space_size_units' => $values[5],//6
                 'size_sf' => $values[6],//7
                 'floor' => $values[7],//8
                 'deal_status'=> $values[8],//9
                 'space_type_id' => $values[9],//10
                 'lease_address' => $values[10],//11
                 'suite'=> $values[11],//12
                 'leasechecksum' => md5(implode('', $values)),//13
                 'lease_desc' => $values[12],//14
                 'lease_type_id'=> $values[13],//15
             ];
         }, $filtered_lease_spaces);
 
         $insert_data = [];
         $update_data = [];
         $update_placeholders = [];
 
         foreach ($extracted_data as $ed) {
             $leasechecksum = $ed['leasechecksum'];
             $lease_id = $ed['lease_id'];
             $deal_stat = $ed['deal_status'];
             $lease_title= false;
             
             if(!empty($ed['lease_address'])){
                 $lease_title = $ed['lease_address'];
                 
                 if($ed['space_size_units']== 'sf' && !empty($ed['size_sf'])){
                     
                     $lease_title .= ' '. number_format($ed['size_sf']) . 'SF';
                 } 
             }
             $existing_record = $wpdb->get_row($wpdb->prepare(
                 "SELECT * FROM $space_tbl WHERE lease_id = %d",
                 $ed['lease_id']
             ));

             if (!$existing_record) {
                 
                 // Collect data for batch insert
                 $insert_data[] = [
                     'lease_id' => $ed['lease_id'],//1
                     'property_id' => $ed['property_id'],//2
                     'lease_title' => $lease_title,//3
                     'lease_rate_units' => $ed['lease_rate_units'],//4
                     'lease_rate' => $ed['lease_rate'],//5
                     'space_size_units' => $ed['space_size_units'],//6
                     'size_sf' => $ed['size_sf'],//7
                     'floor' => $ed['floor'],//8
                     'deal_status' => $ed['deal_status'],//9
                     'space_type_id' => $ed['space_type_id'],//10
                     'lease_address' => $ed['lease_address'],//11
                     'suite' => $ed['suite'],//12
                     'leasechecksum' => $ed['leasechecksum'],//13
                     'lease_desc' => $ed['lease_desc'],//14
                     'lease_type_id' => $ed['lease_type_id'],//15
                 ];
             } else if ($existing_record->leasechecksum !== $leasechecksum) {
                 // Collect data for batch update
                 $update_data[] = [
                     'lease_id' => $lease_id,//1
                     'property_id' => $ed['property_id'],//2
                     'lease_title' =>$lease_title,//3
                     'lease_rate_units' => $ed['lease_rate_units'],//4
                     'lease_rate' => $ed['lease_rate'],//5
                     'space_size_units' => $ed['space_size_units'],//6
                     'size_sf' => $ed['size_sf'],//7
                     'floor' => $ed['floor'],//8
                     'deal_status' => $ed['deal_status'],//9
                     'space_type_id' => $ed['space_type_id'],//10
                     'lease_address' => $ed['address'],//11
                     'suite' => $ed['suite'],//12
                     'leasechecksum' => $ed['leasechecksum'],//13
                     'lease_desc' => $ed['lease_desc'],//14
                     'lease_type_id' => $ed['lease_type_id'],//15
                 ];
                 // Prepare update placeholders
                 $update_placeholders[] = $wpdb->prepare(
                     "(%s, %s, %s, %s, %s, %s, %s, %s ,%s, %s, %s, %s, %s, %s ,%s)",
                     $lease_id,//1
                     $ed['property_id'],//2
                     $ed['lease_title'],//3
                     $ed['lease_rate_units'],//4
                     $ed['lease_rate'],//5
                     $ed['space_size_units'],//6
                     $ed['size_sf'],//7
                     $ed['floor'],//8
                     $ed['deal_status'],//9
                     $ed['space_type_id'],//10
                     $ed['lease_address'],//11
                     $ed['suite'],//12
                     $ed['leasechecksum'],//13
                     $ed['lease_desc'],//14
                     $ed['lease_type_id'],//15
                    
                 );
             }
         }
 
         // Debug logging for collected data
         NEW_np_log('Insert data count: ' . count($insert_data) . "\n");
         NEW_np_log('Update data count: ' . count($update_data) . "\n");
 
         // Batch insert
         if (!empty($insert_data)) {
             foreach ($insert_data as $data) {
                 $result = $wpdb->insert(
                     $space_tbl,
                     $data,
                     array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ,'%s','%s', '%s', '%s', '%s' ,'%s','%s')
                 );
                 if ($result === false) {
                     NEW_np_log('Insert error', $wpdb->last_error);
                 } else {
                     NEW_np_log('Inserted lease_id: ' . $data['lease_id'] . "\n");
                 }
             }
         }
         // Batch update
         if (!empty($update_data)) {
             $query = "INSERT INTO $space_tbl (lease_id,property_id,lease_title, lease_rate_units, lease_rate, space_size_units, size_sf, floor, deal_status,space_type_id, leasechecksum,lease_desc,lease_type_id ) VALUES ";
             $query .= implode(', ', $update_placeholders);
             $query .= " ON DUPLICATE KEY UPDATE lease_id = VALUES(lease_id) , property_id = VALUES(property_id),lease_title = VALUES(lease_title) , lease_rate_units = VALUES(lease_rate_units), lease_rate = VALUES(lease_rate), space_size_units = VALUES(space_size_units), size_sf = VALUES(size_sf), floor=VALUES(floor), deal_status=VALUES(deal_status),space_type_id=values(space_type_id), lease_address=values(lease_address),suite = VALUES(suite),leasechecksum = VALUES(leasechecksum), lease_desc=values(lease_desc),lease_type_id=VALUES(lease_type_id)";
             $result = $wpdb->query($query);
             if ($result === false) {
                 NEW_np_log('Update error', $wpdb->last_error);
             } else {
                 NEW_np_log('Updated lease_id(s): ' . implode(', ', array_column($update_data, 'lease_id')) . "\n");
             }
         }
 
         // Update the checksum in the options table
         update_option('tristatecr_datasync_lease_checksum', $new_checksum);
     } else {
         NEW_np_log('Data checksum matches, no update needed.\n');
     }
 
     NEW_np_log("Data synchronization process completed for lease spaces.\n");
 }
/********************************--Lease Space Sync Ends--*****************************************/
 
/********************************--Properties Sync Start--*****************************************/
   
$message = "\nReading Buildout JSON...";
if (array_intersect(['json'], $skip)) $message .= ' Skipping';
NEW_np_log($message);
if (!array_intersect(['json'], $skip)){
    $limit = 100; 
    $offset = 0;
    $max_retries = 20; 
    $timeout = 30;  
    $total_records = 0;
    $all_properties = [];
    $total_start_prop_time = microtime(true);
    while (true) {
        $attempt = 0;
        $success = false;
        $fetch_start_time = microtime(true);
        while ($attempt < $max_retries && !$success) {
            $response = wp_remote_get('https://buildout.com/api/v1/' . $get_buildout_api_key . '/properties.json?limit=' . $limit . '&offset=' . $offset, array(
                'headers' => array(
                    'Accept' => 'application/json',
                ),
                'timeout' => $timeout  
            ));

            if (is_wp_error($response)) {
                NEW_np_log('Buildout Properties API request failed on attempt ' . ($attempt + 1), $response->get_error_message());
                $attempt++;
                if ($attempt >= $max_retries) {
                    NEW_np_log("Max retries for properties sync reached. Exiting the synchronization process.\n");
                    return;  
                }
            } else {
                $success = true;
            }
        }
        if (200 !== wp_remote_retrieve_response_code($response)) {
            NEW_np_log('Unexpected response code for properties API: ' . wp_remote_retrieve_response_code($response) . "\n");
            break; 
        }

        $properties_data = json_decode(wp_remote_retrieve_body($response));
        $properties = $properties_data->properties;
        
        if (empty($properties)) {
            break; 
        }

        
        $all_properties = array_merge($all_properties, $properties);
      
        $offset += $limit;
        
        $fetch_end_time = microtime(true);
        
        $fetch_time = $fetch_end_time - $fetch_start_time;

        NEW_np_log('Fetched ' . count($properties) . ' properties in ' . $fetch_time . ' seconds. Total so far: ' . count($all_properties) . "\n");
        
    }
  
    $total_end_time = microtime(true);
    
    $total_time = $total_end_time - $total_start_prop_time;
    NEW_np_log('Total time required to fetch all properties: ' . $total_time . ' seconds' . "\n");
    $new_checksum = md5(json_encode($all_properties));
    NEW_np_log('New properties checksum: ' . $new_checksum . "\n");
    
    $space_tbl_name = $wpdb->prefix . 'lease_spaces';
    $lease_space_properties = $wpdb->get_results("SELECT * FROM $space_tbl_name", ARRAY_A);

    
    foreach((object) $all_properties as $item){

            if($item->proposal ) continue;
            $id     = NEW_np_generate_buildout_item_id($item);
            $name = $item->name;
            $checksum = md5(json_encode($item));
            $message = "Processing #$id: \"$name\"";
            defined('WP_CLI') && WP_CLI::log($message);
            $postarr = new_np_process_buildout_item($item);
            $post_id = false;
            if ($found_id = array_search($id, $imported_ids)) {
                $message = '-- Existing post ID ' . $found_id . ' for ' . $id . '.';
                defined('WP_CLI') && WP_CLI::log($message);
                $post_id = $postarr['ID'] = $found_id;
        
                $existing_checksum = $buildout_checksums[$found_id] ?? '';
                if (!$force_update && ($existing_checksum == $checksum)) {
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
        
                $result = wp_update_post($postarr);
                if (is_wp_error($result)) {
                    $message = $result->get_error_message();
                    defined('WP_CLI') && WP_CLI::error($message);
                    $counter['errors']++;
                } else {
                    $message = '--- Updated existing post ID ' . $result;
                    defined('WP_CLI') && WP_CLI::log($message);
                    $counter['updated']++;
                }
            } else {

        // Creating new post for the given ID
        $message = '-- Creating new post for ' . $id . '.';
        defined('WP_CLI') && WP_CLI::log($message);

        $result = wp_insert_post($postarr);
        
        if (is_wp_error($result)) {
            $message = '--- Error creating post: ' . $result->get_error_message();
            defined('WP_CLI') && WP_CLI::error($message);
            $counter['errors']++;
        } else {
        
            $query = $wpdb->prepare(
                "SELECT * FROM $space_tbl_name WHERE property_id = %s AND deal_status=%s",
                $id, '1'
            );
            //gets buildout id not post id 
            $lease_space_properties = $wpdb->get_results($query, ARRAY_A);
            
            $post_id = $result;
            
            $message = '--- Created new post ID ' . $result;
            defined('WP_CLI') && WP_CLI::log($message);
            $counter['imported']++;
            $imported_ids[$post_id] = $id;
            
            if (!empty($lease_space_properties)) {
            
                NEW_np_log('--- Found ' . count($lease_space_properties) . ' Lease Space Properties for "' . get_the_title($result) . '" ---');
                update_post_meta($result,'lease_space_childrens',$lease_space_properties);
                $child_lease_ids = [];
                foreach ($lease_space_properties as $lsp) {
                    $child_lease_ids[] = $lsp['id'];
                }
                
                if(!empty($child_lease_ids)){
                    update_post_meta($result,'lease_space_table_id',$child_lease_ids);
                }
            }
        }

    }
    if ($post_id) {

        update_post_meta($post_id, '_buildout_last_updated', time());
        
    }
    }
}
/********************************--Properties Sync Ends--*****************************************/

/********************************--Google Sheet Sync starts--*****************************************/
    $message = "\nReading Sheets CSV...";
    if (in_array('csv', $skip)) $message .= ' Skipping';
    NEW_np_log($message);
    $filenames = array(
        // https://docs.google.com/spreadsheets/d/1R0-lie_XfdirjxoaXZ59w4etaQPWFBD5c45i-5CaaMk/edit#gid=0
        //'https://docs.google.com/spreadsheets/d/1R0-lie_XfdirjxoaXZ59w4etaQPWFBD5c45i-5CaaMk/gviz/tq?tqx=out:csv&sheet=0',
        // https://docs.google.com/spreadsheets/d/1R0-lie_XfdirjxoaXZ59w4etaQPWFBD5c45i-5CaaMk/edit#gid=1067035268
        //'https://docs.google.com/spreadsheets/d/1nbR6Gxlxdf32sN4wfso51fxEaXktT4plsOzigNS_egw/gviz/tq?tqx=out:csv&sheet=0',
       'https://docs.google.com/spreadsheets/d/1R0-lie_XfdirjxoaXZ59w4etaQPWFBD5c45i-5CaaMk/gviz/tq?tqx=out:csv&sheet=ny',
       'https://docs.google.com/spreadsheets/d/1R0-lie_XfdirjxoaXZ59w4etaQPWFBD5c45i-5CaaMk/gviz/tq?tqx=out:csv&sheet=pa'
    );
    if (!in_array('csv', $skip))
        foreach ($filenames as $fn) {
            defined('DOING_CRON') && update_option(NEW_CRON_STATUS_OPTION, 'Reading Sheets CSV');
            defined('WP_CLI') && WP_CLI::log('Reading ' . $fn . '...');
            if (($handle = fopen($fn, "r")) !== FALSE) {
                $row = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $row++;

                    // Header row
                    if ($row == 1) {
                        $header = $data;
                        array_walk($header, function (&$item) {
                            $item = sanitize_title($item);
                            $item = strtolower(str_replace('-', '_', $item));
                        });
                        continue;
                    }

                    // Data row
                    $item         = (object) array_combine($header, $data);
                    $id             = new_np_generate_google_csv_item_id($item);
                    $checksum = md5(json_encode($item));
                    $message = "- Processing #$id";
                    defined('WP_CLI') && WP_CLI::log($message);

                    if ($buildout_id = $item->buildout_id ?? false) {
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
                    if (!$post_id) {
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

                    if (!$force_update && ($post_sheet_checksum && $post_sheet_checksum == $checksum)) {
                        $message = "--- No changes detected, checksum $checksum matches.";
                        defined('WP_CLI') && WP_CLI::log($message);
                        $message = "--- Skipping.";
                        defined('WP_CLI') && WP_CLI::log($message);
                        continue;
                    } else {
                        $message = "--- Changes detected, checksum $checksum does not match $post_sheet_checksum";
                        defined('WP_CLI') && WP_CLI::log($message);
                    }

                    $sheet_meta = new_np_process_google_csv_item_meta($item);

                    // Update the post meta
                    $message = "--- Updating post_meta for post_id:$post_id buildout_id:$buildout_id";
                    defined('WP_CLI') && WP_CLI::log($message);

                    foreach ($sheet_meta as $key => $value) {
                        // $message = "---- Updating $key to $value";
                        // defined('WP_CLI') && WP_CLI::log($message);
                        // update_post_meta($post_id, $key, $value);
                        // update_post_meta($post_id, '_gsheet_last_updated', time());
                        $message = "---- Updating $key to $value";
                        defined('WP_CLI') && WP_CLI::log($message);
                        update_post_meta($post_id, $key, $value);
                        if ($key == '_gsheet_min_size') {
                            $new_min_val = (float) preg_replace('/[^0-9.]/', '', $value);
                            update_post_meta($post_id, '_gsheet_min_size_fm',$new_min_val);
                        }
                        if ($key == '_gsheet_min_size') {
                            $new_min_val = (float) preg_replace('/[^0-9.]/', '', $value);
                            update_post_meta($post_id, '_gsheet_min_size_fm',$new_min_val);
                        }
                        if($key == '_gsheet_state'){
                            update_post_meta($post_id, '_gsheet_state',strtoupper($value));
                        }
                        if ($key == '_gsheet_max_size') {
                            $new_max_val =    (float) preg_replace('/[^0-9.]/', '', $value);
                            update_post_meta($post_id, '_gsheet__max_size_fm',$new_max_val);
                        }
                        
                        if($key == '_gsheet_monthly_rent'){
                            $newmnthrent = (float) preg_replace('/[^0-9.]/', '', $value);
                            update_post_meta($post_id, '__gsheet__monthly_rent',$newmnthrent);
                        
                        }
                        
                        update_post_meta($post_id, '_gsheet_last_updated', time());
                    }

                    $counter['matched']++;
                }
                fclose($handle);
            }

            $message = 'Found ' . $row . ' records in ' . $fn . '.';
            NEW_np_log($message);
        }
/********************************--Google Sheet Sync ends--*****************************************/
    $message = "Counters: " . print_r($counter, true);
    defined('WP_CLI') && WP_CLI::log($message);
    // add brokers into custom post type called brokers
    new_tristatecr_insert_broker_data();
    defined('DOING_CRON') && update_option(NEW_CRON_STATUS_OPTION, 'Done');
    defined('DOING_CRON') && update_option(NEW_CRON_LAST_RESULT_OPTION, $counter);

    $secs = microtime(true) - $start;
    $message = "Done in $secs seconds.";
    NEW_np_log($message);

    return $counter;
}
defined('WP_CLI') && WP_CLI::add_command('datasync', 'tristatectr_datasync_command_v2');


function NEW_np_log($message, $data = null)
{
    if (!is_null($data)) $message . ' ' . print_r($data, 1);

    if (defined('WP_CLI')) {
        WP_CLI::log($message);
    } else {
        error_log($message, 3, NEW_LOG_FILE);
    }
}

function new_tristatectr_get_brokers_with_excluded($broker_ids = array())
{
    $brokers = (array) get_option('tristatecr_datasync_brokers');
    $excluded_brokers = array('Shlomi Bagdadi');

    $array = array();
    foreach ($broker_ids as $broker_id) {
        $array[$broker_id] = $brokers[$broker_id] ?? $broker_id;
    }
    if (count($array) > 1) {
        $array = array_diff($array, $excluded_brokers);
    }
    return array_filter($array);
}


function NEW_np_generate_buildout_item_id($data = null)
{
    return (int) $data->id;
}


function new_np_process_buildout_item($data = null)
{
    $result = array();
    $result['post_title']     = $data->name ?? '';
    $result['post_content'] = $data->description ?? '';
    $result['post_excerpt'] = $data->location_description ?? '';
    $result['post_status']     = 'publish';
    $result['post_type']     = 'properties';
    $result['tax_input']     = array();
    $result['meta_input']     = new_np_process_buildout_item_meta($data);

    // defined('WP_CLI') && WP_CLI::log(print_r(array_keys((array) $data), 1));
    return $result;
}

function new_np_process_buildout_item_meta($data = null)
{
    $checksum = md5(json_encode($data));
    $result = array(
        '_import_buildout_id'             => NEW_np_generate_buildout_item_id($data),
        '_import_from'                             => 'buildout',
        '_import_buildout_checksum' => $checksum,
    );

    $props = array(
        'id',
        'broker_id',
        'second_broker_id',
        'third_broker_id',
        'broker_ids',
        'address',
        'city',
        'state',
        'zip',
        'county',
        'country_code',
        'country_name',
        'hide_address',
        'hide_address_label_override',
        'market',
        'submarket',
        'cross_streets',
        'location_description',
        'latitude',
        'longitude',
        'custom_lat_lng',
        'name',
        'property_type_id',
        'property_type_label_override',
        'property_subtype_id',
        'additional_property_subtype_ids',
        'apn',
        'zoning',
        'lot_size_acres',
        'you_tube_url',
        'mls_id',
        'ceiling_height_f',
        'ceiling_height_min',
        'renovated',
        'parking_ratio',
        'number_of_parking_spaces',
        'utilities_description',
        'traffic_count',
        'traffic_count_street',
        'traffic_count_frontage',
        'site_description',
        'grade_level_doors',
        'dock_high_doors',
        'number_of_cranes',
        'sprinkler_description',
        'power_description',
        'industrial_office_space',
        'display_locale_override',
        'currency_key',
        'currency_format',
        'measurements',
        'building_size_sf',
        'number_of_units',
        'number_of_floors',
        'year_built',
        'occupancy_pct',
        'building_class',
        'gross_leasable_area',
        'proposal',
        'sale',
        'sale_deal_status_id',
        'auction',
        'auction_date',
        'auction_time',
        'auction_location',
        'auction_starting_bid_dollars',
        'auction_url',
        'distressed',
        'hide_sale_price',
        'hidden_price_label',
        'sale_price_dollars',
        'sale_price_per_unit',
        'sale_price_units',
        'sale_title',
        'sale_description',
        'sale_expiration_date',
        'sale_bullets',
        'sale_pdf_url',
        'property_use_id',
        'tenancy_id',
        'cap_rate_pct',
        'net_operating_income',
        'land_legal_description',
        'parking_type_id',
        'elevators',
        'sprinklers',
        'construction_status_id',
        'walls',
        'roof',
        'crane_description',
        'taxes',
        'frontage',
        'lot_depth',
        'number_of_buildings',
        'number_of_lots',
        'best_use',
        'irrigation',
        'irrigation_description',
        'water',
        'water_description',
        'telephone',
        'telephone_description',
        'cable',
        'cable_description',
        'gas',
        'gas_description',
        'sewer',
        'environmental_issues',
        'topography',
        'soil_type',
        'easements_description',
        'foundation',
        'framing',
        'exterior_description',
        'hvac',
        'parking_description',
        'landscaping',
        'rail_access',
        'column_space',
        'dock_door_description',
        'drive_in_bays',
        'trailer_parking',
        'amenities',
        'days_on_market',
        'lease',
        'lease_deal_status_ids',
        'lease_title',
        'lease_description',
        'lease_bullets',
        'lease_pdf_url',
        'lease_expiration_date',
        'leed_certified',
        'photos',
        'documents',
        'matterport_url',
        'sale_listing_url',
        'sale_listing_published',
        'sale_listing_searchable',
        'sale_listing_slug',
        'sale_listing_web_title',
        'sale_listing_web_description',
        'lease_listing_url',
        'lease_listing_published',
        'lease_listing_searchable',
        'lease_listing_slug',
        'lease_listing_web_title',
        'lease_listing_web_description',
        'draft',
        'notes',
        'external_id',
        'gross_scheduled_income',
        'other_income',
        'operating_expenses',
        'vacancy_cost',
        'down_payment',
        'cash_on_cash',
        'total_return',
        'debt_service',
        'principal_reduction_yr_1',
        'rev_par',
        'adr',
        'nearest_highway',
        'load_factor',
        'restrooms',
        'mapright_embed_code',
        'custom_fields',
        'created_at',
        'updated_at',
    );

    foreach ($props as $prop) {
        $key = '_buildout_' . $prop;
        $result[$key] = $data->$prop ?? '';
    }

    return array_filter($result);
}

function new_np_generate_google_csv_item_id($data = null)
{
    $fields = array(
        $data->address ?? '',
        $data->cross_street ?? '',
        $data->neighborhood ?? '',
        $data->key_tag ?? '',
    );
    return sanitize_title(implode('-', $fields));
}

function new_np_process_google_csv_item_meta($data = null)
{
    $checksum = md5(json_encode($data));
    $result = array(
        '_import_from'                         => 'sheets',
        '_import_gsheet_checksum' => $checksum,
    );

    $props = array_keys(get_object_vars($data));

    foreach ($props as $prop) {
        $key = '_gsheet_' . $prop;
        $result[$key] = $data->$prop ?? '';
    }

    return array_filter($result);
}

// Register a custom interval for every two days
add_filter('cron_schedules', 'tristatecr_syncapi_cron_schedules');
function tristatecr_syncapi_cron_schedules($schedules) {
    $schedules['every_two_days'] = array(
        'interval' => 2 * DAY_IN_SECONDS,
        'display'  => __('Every Two days'),
    );
    return $schedules;
}

// Schedule the event if not already scheduled
add_action('wp', 'schedule_tristatecr_syncapi_cron_job');
function schedule_tristatecr_syncapi_cron_job() {
    if (!wp_next_scheduled('tristatecr_syncapi_cron_job')) {
        wp_schedule_event(time(), 'every_two_days', 'tristatecr_syncapi_cron_job');
    }
}

// Define the callback function
function tristatecr_syncapi_cron_job_function() {
    // Your custom code here
    update_option('tristatecr_datasync_cron_last_startedv2', time());

    $time = time();
    $datetime = date('Y-m-d H:i:s', $time);

    $message = "Running new_tristatecr_datasync_cron_function at $datetime\n";
    error_log($message, 3, NEW_LOG_FILE);

    $args = array();
    $aargs = array(
        'skip' => 'remote',
    );
    $result = tristatectr_datasync_command_v2($args, $aargs);
  
    $message = "\nResults: " . print_r($result, 1) . "\n";
    error_log($message, 3, NEW_LOG_FILE);

    $args = array(
        'post_type' => 'properties',
        'post_status' => 'publish',
        'posts_per_page' => -1 
    );

    $query = new WP_Query($args);

    if($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $buildout_agent = get_post_meta(get_the_id(), '_buildout_broker_id', true);
            if(!empty($buildout_agent)){
                global $wpdb;
                $agquery = $wpdb->prepare(
                    "SELECT pm.post_id 
                     FROM $wpdb->postmeta pm
                     INNER JOIN $wpdb->posts p ON pm.post_id = p.ID
                     WHERE pm.meta_key = 'user_id' 
                       AND pm.meta_value = %s
                       AND p.post_type = 'brokers'",
                    $buildout_agent
                );
                $agent_id = $wpdb->get_var($agquery);

                $_agent = get_the_title($agent_id);

                update_post_meta(get_the_id(),'_buildout_listing_agent', $_agent);
            }
        }
        wp_reset_postdata();
    }

    $message = "\nResults: " . print_r($result, 1) . "\n";
    error_log($message, 3, NEW_LOG_FILE);
}
add_action('tristatecr_syncapi_cron_job', 'tristatecr_syncapi_cron_job_function');

/* ----------------------------------Broker stup code form api data----------------------- */

function new_user_exists_as_broker($user_id)
{
    $args = array(
        'post_type' => 'brokers',
        'meta_query' => array(
            array(
                'key' => 'user_id',
                'value' => $user_id,
            ),
        ),
    );

    $query = new WP_Query($args);
    return $query->have_posts();
}

// Insert data into custom post type "brokers"
/**
 * The function `insert_broker_data` inserts broker data into a custom post type if the broker does not
 * already exist.
 */
function new_tristatecr_insert_broker_data()
{

    $datas = get_option('tristatecr_datasync_brokers');

    if ($datas) {
        foreach ($datas as $key => $data) {
            $user_id = $data['id'];
            $broker_user_title = $data['first_name'] . ' ' . $data['last_name'];
            $broker_email = $data['email'];
            $broker_address = $data['address'];
            $broker_job_title = $data['job_title'];
            $broker_phone_number = $data['phone_number'];
            $broker_profile_pic = $data['profile_photo_url'];


            // Check if user already exists as a broker
            if (!new_user_exists_as_broker($user_id)) {
                // User does not exist as a broker, insert data into "brokers" custom post type
                $post_data = array(
                    'post_title' => $broker_user_title,
                    'post_content' => '',
                    'post_type' => 'brokers',
                    'post_status' => 'publish',
                    // Add any additional fields as needed
                    'meta_input' => array(
                        'user_id' => $user_id,
                        'broker_email' => $broker_email,
                        'broker_address' => $broker_address,
                        'broker_job_title' => $broker_job_title,
                        'broker_phone_number' => $broker_phone_number,
                        'broker_profile_pic' => $broker_profile_pic,
                        // Additional meta fields
                    ),
                );

                wp_insert_post($post_data);
            }
        }
    }
}




function new_tristate_get_broker_id($meta_key, $meta_value)
{

    $posts = get_posts(array(
        'post_type' => 'brokers', // Change 'broker' to your custom post type
        'meta_key' => $meta_key,
        'meta_value' => $meta_value,
        'posts_per_page' => -1, // Retrieve all matching posts
        'fields' => 'ids', // Only retrieve post IDs
    ));

    return ($posts[0]);
}


function get_lease_space_data($pid){
    global $wpdb;
    $space_tbl= $wpdb->prefix . 'lease_spaces';
    $l_meta = get_post_meta($pid,'lease_space_table_id',true);
    $html = '';
    if(!empty($l_meta)){
        $html .= "<div class='trimmed-unit'>";
        $html .= "<ul class='ul-content ul-features>";
        $counter=1;
        foreach($l_meta as $lm){
            $query = $wpdb->prepare(
                "SELECT * FROM $space_tbl WHERE id = %s AND deal_status=%s",
                $lm, '1'
            );
            $row = $wpdb->get_row($query, ARRAY_A);
            // var_dump($row);
            $html .= '<h4>Unit ' . $counter .'</h4>';
            $html .= "<li><p>Title: <span>Unit title</span></p></li>";
            $html .=  "<li><p>Price: <span>$1234 SF</span></p> </li>";
            $html .= "<li><p>Area: <span>600 SF</span></p></li>";
            $counter ++;
        }
        $html .= "</ul>";
        $html .= "</div>";
    }
    
    return $html;

}