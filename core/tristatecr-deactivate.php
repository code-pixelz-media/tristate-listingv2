<?php 


add_action('wp_footer', function(){

    if(isset($_GET['reset']) && $_GET['reset'] ==1){
        tristate_plugin_deactivate();
    }

});

function tristate_plugin_deactivate() {
    // Call the function to delete posts of custom post types
    tri_cr_delete_custom_posts('properties');
    tri_cr_delete_custom_posts('brokers');
    tri_cr_delete_specific_meta_keys();
    tri_cr_delete_specific_options();
    tri_cr_delete_table();
}
function tri_cr_delete_table() {
    global $wpdb;
    $tbl = $wpdb->prefix . 'lease_spaces';
    $wpdb->query("TRUNCATE TABLE $tbl");
}


function tri_cr_delete_custom_posts($post_type) {
    // Get all posts of the specified custom post type
    $args = array(
        'post_type' => $post_type,
        'posts_per_page' => -1,
        'post_status' => 'any'
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            // Delete post meta
            tri_cr_delete_post_meta_by_key($post_id);

            // Delete the post
            wp_delete_post($post_id, true);
        }
    }

    // Reset post data
    wp_reset_postdata();
}

function tri_cr_delete_post_meta_by_key($post_id) {
    // Get all post meta keys for the post
    $meta_keys = get_post_custom_keys($post_id);

    if ($meta_keys) {
        foreach ($meta_keys as $meta_key) {
            delete_post_meta($post_id, $meta_key);
        }
    }
}

function tri_cr_delete_specific_meta_keys() {
    global $wpdb;

    $meta_keys_to_delete = array(
        '_import_buildout_id',
        '_import_buildout_checksum',
        '_import_gsheet_checksum'
    );

    foreach ($meta_keys_to_delete as $meta_key) {
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->postmeta WHERE meta_key = %s",
                $meta_key
            )
        );
    }
}

function tri_cr_delete_specific_options() {
    $options_to_delete = array(
        'tristatecr_datasync_cron_last_started',
        'tristate_cron_status',
        'tristatecr_datasync_brokers_checksum',
        'tristatecr_datasync_brokers',
        'tristatecr_datasync_lease_checksum'
    );

    foreach ($options_to_delete as $option) {
        delete_option($option);
    }
}