<?php

global $post;
global $property;
global $propertyhive_loop;
global $wpdb;

$context = Timber::context();

// $featured_query = [
//     'posts_per_page' => 4,
//     'post_type' => 'property',
//     'meta_query'  => array(
//         array(
//             'key' => '_featured',
//             'value' => 'yes'
//         )
//     )
// ];

$context['post'] = new Timber\Post(8);
$context['properties'] = new Timber\PostQuery();

// $context['featured_properties'] = new Timber\PostQuery($featured_query);

Timber::render( [ '_propertyhive/archive-property.twig', 'page.twig' ], $context );