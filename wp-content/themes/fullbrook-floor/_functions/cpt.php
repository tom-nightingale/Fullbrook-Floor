<?php 


/* ========================================================================================================================

Register Locations CPT

======================================================================================================================== */
// function register_locations() {

// 	$labels = array(
// 		'name'                  => _x( 'Locations', 'Post Type General Name', 'text_domain' ),
// 		'singular_name'         => _x( 'Location', 'Post Type Singular Name', 'text_domain' ),
// 		'menu_name'             => __( 'Locations', 'text_domain' ),
// 		'name_admin_bar'        => __( 'Locations', 'text_domain' ),
// 		'archives'              => __( 'Location Archives', 'text_domain' ),
// 		'attributes'            => __( 'Location Attributes', 'text_domain' ),
// 		'parent_item_colon'     => __( 'Parent Location:', 'text_domain' ),
// 		'all_items'             => __( 'All Locations', 'text_domain' ),
// 		'add_new_item'          => __( 'Add New Location', 'text_domain' ),
// 		'add_new'               => __( 'Add New', 'text_domain' ),
// 		'new_item'              => __( 'New Location', 'text_domain' ),
// 		'edit_item'             => __( 'Edit Location', 'text_domain' ),
// 		'update_item'           => __( 'Update Location', 'text_domain' ),
// 		'view_item'             => __( 'View Location', 'text_domain' ),
// 		'view_items'            => __( 'View Locations', 'text_domain' ),
// 		'search_items'          => __( 'Search Location', 'text_domain' ),
// 		'not_found'             => __( 'Not found', 'text_domain' ),
// 		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
// 		'featured_image'        => __( 'Location Image', 'text_domain' ),
// 		'set_featured_image'    => __( 'Set location image', 'text_domain' ),
// 		'remove_featured_image' => __( 'Remove location image', 'text_domain' ),
// 		'use_featured_image'    => __( 'Use as location image', 'text_domain' ),
// 		'insert_into_item'      => __( 'Insert into location', 'text_domain' ),
// 		'uploaded_to_this_item' => __( 'Uploaded to this location', 'text_domain' ),
// 		'items_list'            => __( 'Locations list', 'text_domain' ),
// 		'items_list_navigation' => __( 'Locations list navigation', 'text_domain' ),
// 		'filter_items_list'     => __( 'Filter locations list', 'text_domain' ),
// 	);
// 	$rewrite = array(
// 		'slug'                  => 'locations',
// 		'with_front'            => false,
// 		'pages'                 => true,
// 		'feeds'                 => true,
// 	);
// 	$args = array(
// 		'label'                 => __( 'Location', 'text_domain' ),
// 		'description'           => __( 'Locations CPT', 'text_domain' ),
// 		'labels'                => $labels,
// 		'supports'              => array( 'title', 'editor', 'thumbnail' ),
// 		'hierarchical'          => false,
// 		'public'                => true,
// 		'show_ui'               => true,
// 		'show_in_menu'          => true,
// 		'menu_position'         => 5,
// 		'menu_icon'             => 'dashicons-location',
// 		'show_in_admin_bar'     => true,
// 		'show_in_nav_menus'     => true,
// 		'can_export'            => true,
// 		'has_archive'           => false,
// 		'exclude_from_search'   => false,
// 		'publicly_queryable'    => true,
// 		'rewrite'               => $rewrite,
// 		'capability_type'       => 'page',
// 	);
// 	register_post_type( 'locations', $args );

// }
// add_action( 'init', 'register_locations', 0 );



/* ========================================================================================================================

Register Help & Advice CPT

======================================================================================================================== */
function register_help_advice() {

	$labels = array(
		'name'                  => _x( 'Help & Advice', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'Help & Advice', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Help & Advice', 'text_domain' ),
		'name_admin_bar'        => __( 'Help & Advice', 'text_domain' ),
		'archives'              => __( 'Help & Advice Archives', 'text_domain' ),
		'attributes'            => __( 'Help & Advice Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Help & Advice:', 'text_domain' ),
		'all_items'             => __( 'All Help & Advice', 'text_domain' ),
		'add_new_item'          => __( 'Add New Help & Advice', 'text_domain' ),
		'add_new'               => __( 'Add Help & Advice', 'text_domain' ),
		'new_item'              => __( 'New Help & Advice', 'text_domain' ),
		'edit_item'             => __( 'Edit Help & Advice', 'text_domain' ),
		'update_item'           => __( 'Update Help & Advice', 'text_domain' ),
		'view_item'             => __( 'View Help & Advice', 'text_domain' ),
		'view_items'            => __( 'View Help & Advice', 'text_domain' ),
		'search_items'          => __( 'Search Help & Advice', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Help & Advice Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set Help & Advice image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove Help & Advice image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as Help & Advice image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into Help & Advice', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this Help & Advice', 'text_domain' ),
		'items_list'            => __( 'Help & Advice list', 'text_domain' ),
		'items_list_navigation' => __( 'Help & Advice list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter Help & Advice list', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'Help & Advice', 'text_domain' ),
		'description'           => __( 'Help & Advice CPT', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail' ),
		'taxonomies'            => array( 'help-advice-categories' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-index-card',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
    'capability_type'       => 'post',
    'rewrite'               => [ 'with_front' => false ],
	);
	register_post_type( 'help-advice', $args );

}
add_action( 'init', 'register_help_advice', 0 );

?>