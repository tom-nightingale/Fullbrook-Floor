<?php

$context = Timber::context();
$context['post'] = new Timber\Post();

$for_sale_query = [
    'posts_per_page' => 4,
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

$sold = [
    'posts_per_page' => 4,
    'orderby' => 'date',
    'post_type' => 'property',
    'tax_query' => array (
        array (
            'taxonomy' => 'availability',
            'field' => 'slug',
            'terms' => 'under-offer',
        )
    )
];

$for_sale = new Timber\PostQuery($for_sale_query);
$sold = new Timber\PostQuery($sold);

$featured_posts = array_merge($for_sale->get_posts(), $sold->get_posts());

$context['featured_properties'] = $featured_posts;

Timber::render( [ 'front-page.twig' ], $context );
