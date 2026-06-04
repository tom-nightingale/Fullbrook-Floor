<?php 
add_action( 'propertyhive_property_imported_jet', 'amend_property_slug', 10, 2);
function amend_property_slug( $post_id, $property )
{
    // $slug = $property['AGENT_REF'];
    $slug = $property->ID;
    $newSlug = str_replace("rps_faf-", "", $slug);
    $my_post = array(
        'ID'             => $post_id,
        'post_name'      => sanitize_title($newSlug),
    );

    // Update the post into the database
    wp_update_post( $my_post );
}