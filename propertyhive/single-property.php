<?php

global $post;
global $property;
global $propertyhive_loop;
global $wpdb;

$context = Timber::context();

$terms = [
    'property_type',
    'outside_space',
    'parking',
    'marketing_flag',
    'furnished',
    'tenure',
    'availability',
];

foreach($terms as $term) {
    $context[$term] = get_the_terms($post->ID, $term);
}

$context['post'] = new Timber\Post();

Timber::render( [ '_propertyhive/single-property.twig', 'page.twig' ], $context );