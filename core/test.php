<?php
if(!empty($lease_space_properties)){
                // Creating new Lease Space Properties
    foreach($lease_space_properties as $lsp){
        $lease_postarr = $postarr;
        $lease_postarr['post_title'] = $name . ' - Unit ' . $lsp['id'];
        $lease_postarr['meta_input'] = array_merge($postarr['meta_input'], [
            'lease_rate_units' => $lsp['lease_rate_units'],
            'lease_rate' => $lsp['lease_rate'],
            'space_size_units' => $lsp['space_size_units'],
            'size_sf' => $lsp['size_sf'],
            'floor' => $lsp['floor']
        ]);
        $lease_result = wp_insert_post($lease_postarr);
        if (is_wp_error($lease_result)) {
            $message = '--- Error creating Lease Space Property post: ' . $lease_result->get_error_message();
            defined('WP_CLI') && WP_CLI::error($message);
        } else {
            $message = '--- Created Lease Space Property post ID ' . $lease_result;
            defined('WP_CLI') && WP_CLI::log($message);
        }
    }
}