<?php

// Register our content types
function np_setup_post_type() {
	$args = array(
			'public' 				=> true,
			'label' 				=> __( 'Properties', 'textdomain' ),
			'menu_icon' 		=> 'dashicons-location',
			'has_archive' 	=> true,
			'rewrite' 			=> array( 'slug' => 'listings' ),
			'menu_position' => 5,
	);
	register_post_type( 'tsc_property', $args );

	$args = array(
			'public' 				=> true,
			'label' 				=> __( 'Searches', 'textdomain' ),
			'menu_icon' 		=> 'dashicons-search',
			'has_archive' 	=> false,
			'rewrite' 			=> array( 'slug' => 'searches' ),
			'menu_position' => 5,
			'show_in_menu' 	=> 'edit.php?post_type=tsc_property',
			'supports' 			=> array( 'title', 'author', 'custom-fields' ),
	);
	register_post_type( 'tsc_search', $args );
}
add_action( 'init', 'np_setup_post_type' );