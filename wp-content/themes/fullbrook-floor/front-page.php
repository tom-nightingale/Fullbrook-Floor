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

$under_offer = [
    'posts_per_page' => 4,
    'orderby' => 'date',
    'post_type' => 'property',
    'tax_query' => array (
        array (
            'taxonomy' => 'availability',
            'field' => 'slug',
            'terms' => ['under-offer', 'sold'],
        )
    ),
    'meta_query' => array(
        array(
            'key'     => '_on_market',
            'value'   => array( 'Yes' ),
            'compare' => 'IN',
        ),
    ),
];

$for_sale = new Timber\PostQuery($for_sale_query);
$under_offer = new Timber\PostQuery($under_offer);

$featured_posts = array_merge($for_sale->get_posts(), $under_offer->get_posts());

$context['featured_properties'] = $featured_posts;

Timber::render( [ 'front-page.twig' ], $context );
