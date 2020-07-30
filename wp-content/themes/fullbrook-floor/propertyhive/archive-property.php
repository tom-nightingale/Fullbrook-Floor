<?php

global $property;
global $wpdb;

$context = Timber::context();

$featured_query = [
    'posts_per_page' => 4,
    'post_type' => 'property',
    'meta_query'  => array(
        array(
            'key' => '_featured',
            'value' => 'yes'
        )
    )
];

$buy_a_home = new Timber\Post(8);
$properties = new Timber\PostQuery();
$featured_properties = new Timber\PostQuery($featured_query);

$context['post'] = $buy_a_home;
$context['properties'] = $properties;

$context['featured_properties'] = $featured_properties;

Timber::render( [ '_propertyhive/archive-property.twig', 'page.twig' ], $context );