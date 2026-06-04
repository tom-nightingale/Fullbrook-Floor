<?php

add_action('after_setup_theme', function () {
    // Hide the admin bar.
    show_admin_bar(false);

    // Enable plugins to manage the document title
    add_theme_support('title-tag');

    // Enable post thumbnails
    add_theme_support('post-thumbnails');

    // Enable HTML5 markup support
    add_theme_support('html5', ['caption', 'comment-form', 'comment-list', 'gallery', 'search-form']);

    // Register wp_nav_menu() menus
    register_nav_menus([
        'primary' => __('Primary Menu', 'league'),
        'footer' => __('Footer Menu', 'league')
    ]);
});


if (! function_exists('atk_excerpt_more')) {
    add_filter('excerpt_more', function () {
        return '...';
    });
}

add_filter('upload_mimes', function($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
});

/**
 * Wrap embedded media as suggested by Readability
 *
 * @link https://gist.github.com/965956
 * @link http://www.readability.com/publishers/guidelines#publisher
 */
add_filter('embed_oembed_html', function ($cache) {
	return '<div class="entry-content-asset">' . $cache . '</div>';
});

function remove_default_description($bloginfo) {
  $default_tagline = 'Just another WordPress site';
  return ($bloginfo === $default_tagline) ? '' : $bloginfo;
}
add_filter('get_bloginfo_rss', 'remove_default_description');

?>