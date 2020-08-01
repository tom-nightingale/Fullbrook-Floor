<?php

global $post;
global $property;
global $propertyhive_loop;
global $wpdb;

$context = Timber::context();

$context['post'] = new Timber\Post();

Timber::render( [ '_propertyhive/single-property.twig', 'page.twig' ], $context );