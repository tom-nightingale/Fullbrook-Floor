<?php

$context = Timber::context();
$context['post'] = new Timber\Post();

$featured_query = [
    'posts_per_page' => 3,
    'orderby' => 'date',
    'post_type' => 'property',
    // 'meta_query'  => array(
    //     array(
    //         'key' => '_featured',
    //         'value' => 'yes'
    //     )
    // )
];
$featured_properties = new Timber\PostQuery($featured_query);
$context['featured_properties'] = $featured_properties;

Timber::render( [ 'front-page.twig' ], $context );
