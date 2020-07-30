<?php

global $property;
global $wpdb;

$context = Timber::context();

$buy_a_home = new Timber\Post(8);
$properties = new Timber\PostQuery();

$context['post'] = $buy_a_home;
$context['properties'] = $properties;

Timber::render( [ '_propertyhive/archive-property.twig', 'page.twig' ], $context );