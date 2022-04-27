<?php

global $post;
global $property;
global $propertyhive_loop;
global $wpdb;

$context = Timber::context();

$featured_query = [
    'posts_per_page' => 4,
    'orderby' => 'rand',
    'post_type' => 'property',
    'meta_query'  => array(
        array(
            'key' => '_featured',
            'value' => 'yes'
        )
    )
];

// Check to see if we're on the first page of homes so we can show the featured properties
// Check against the query string for search, and check against pages
$query_string = $_GET;
if(!$query_string && $paged == 0) {
    $context['featured_properties'] = new Timber\PostQuery($featured_query);
}

if($query_string) {
    $context['query'] = true;
}
$context['post'] = new Timber\Post(8);
$context['properties'] = new Timber\PostQuery();


Timber::render( [ '_propertyhive/archive-property.twig', 'page.twig' ], $context );
