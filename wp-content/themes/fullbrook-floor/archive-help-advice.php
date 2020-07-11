<?php

$context = Timber::context();

$help_advice_categories = [
  'taxonomy' => 'help-advice-categories',
  'hide_empty' => false
];

$context['posts'] = new Timber\PostQuery();
$context['help_advice_categories'] = Timber::get_terms($help_advice_categories);

Timber::render( [ 'archive-help-advice.twig', 'archive.twig' ], $context );
