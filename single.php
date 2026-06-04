<?php

$context = Timber::context();
$timber_post = new Timber\Post();

$help_advice_categories = [
  'taxonomy' => 'help-advice-categories',
  'hide_empty' => false
];


$context['post'] = $timber_post;
$context['help_advice_categories'] = Timber::get_terms($help_advice_categories);
Timber::render( [ 'single-'.$timber_post->slug.'.twig', 'single.twig' ], $context );
