<?php

$context = Timber::context();
$context['post'] = new Timber\Post();

$for_sale_query = [
    'posts_per_page' => 100,
    'post_type' => 'property',
    'tax_query' => [
        [
            'taxonomy' => 'availability',
            'field' => 'slug',
            'terms' => ['for-sale'],
        ],
    ],
    'meta_query' => [
        'relation' => 'AND',
        [
            'key'     => '_on_market',
            'value'   => 'Yes',
            'compare' => '=',
        ],
        [
            'key'     => '_address_concatenated',
            'value'   => 'Hitchin',
            'compare' => 'LIKE',
        ],
    ],
];
$under_offer_query = [
    'posts_per_page' => 100,
    'post_type' => 'property',
    'tax_query' => [
        [
            'taxonomy' => 'availability',
            'field' => 'slug',
            'terms' => ['under-offer', 'sold'],
        ],
    ],
    'meta_query' => [
        'relation' => 'AND',
        [
            'key'     => '_on_market',
            'value'   => 'Yes',
            'compare' => '=',
        ],
        [
            'key'     => '_address_concatenated',
            'value'   => 'Hitchin',
            'compare' => 'LIKE',
        ],
    ],
];

$for_sale = new Timber\PostQuery($for_sale_query);
$under_offer = new Timber\PostQuery($under_offer_query);

$featured_posts = array_merge($for_sale->get_posts(), $under_offer->get_posts());

$context['featured_properties'] = $featured_posts;

Timber::render( [ 'page-estate-agents-hitchin.twig' ], $context );
