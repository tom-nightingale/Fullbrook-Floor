<?php

$context = Timber::context();
$timber_post = new Timber\Post(8);
$context['post'] = $timber_post;

Timber::render( [ '_propertyhive/archive-property.twig', 'page.twig' ], $context );