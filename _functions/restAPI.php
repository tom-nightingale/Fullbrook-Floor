<?php

  //Register a new Rest API endpoint for the livesearch
  add_action( 'rest_api_init', 'liveSearch' );
  function liveSearch() {
    register_rest_route( 'livesearch/v2', '/livesearch', array(
        'methods'  => 'GET',
        'callback' => 'rest_api_livesearch',
    ) );
  }

  function rest_api_livesearch($request) {
    $query = filter_var($request['search_term'], FILTER_SANITIZE_STRING);
    $suggestions = [];

    $resources = [
      'post_type' => 'help-advice',
      'post_status' => 'publish',
      'posts_per_page' => 5,
      'orderby' => 'title',
      'order' => 'ASC',
      's' => $query,
    ];

    $return_resources = new WP_Query($resources);

    if($return_resources->have_posts()) {
      foreach($return_resources->posts as $resource) {
        $resource_id = $resource->ID;
        $resource_name = $resource->post_title;
        $resource_link = get_permalink($resource->ID);

        $suggestions[$resource_id] = [
          'title'     =>  $resource_name,
          'url'       =>  $resource_link,
        ];
      }
    }

    $context = [];
    $context['livesearch'] = $suggestions;
    $template = Timber::compile( '_components/livesearch.twig', $context);
    return $template;

  }