<?php

$context = Timber::context();
$timber_post = new Timber\Post();
$context['post'] = $timber_post;

Timber::render( [ '_propertyhive/archive-property.twig', 'page.twig' ], $context );