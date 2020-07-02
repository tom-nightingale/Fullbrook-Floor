<?php

$context = Timber::context();

$help_advice_categories = [
  'taxonomy' => 'help-advice-categories',
  'hide_empty' => false
];

$term = get_queried_object();

$context['posts'] = Timber::get_posts();
$context['current_term'] = new Timber\Term($term);
$context['help_advice_categories'] = Timber::get_terms($help_advice_categories);

Timber::render( [ 'taxonomy-'.$taxonomy.'.twig', 'taxonomy.twig' ], $context );
