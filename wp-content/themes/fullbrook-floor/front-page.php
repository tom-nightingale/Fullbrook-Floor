<?php

$context = Timber::context();
$context['post'] = new Timber\Post();

$featured_query = [
    'posts_per_page' => -1,
    'orderby' => 'date',
    'post_type' => 'property',
    'tax_query' => array (
        array (
            'taxonomy' => 'availability',
            'field' => 'slug',
            'terms' => 'for-sale',
        )
    )
];
$featured_properties = new Timber\PostQuery($featured_query);
$context['featured_properties'] = $featured_properties;

Timber::render( [ 'front-page.twig' ], $context );
