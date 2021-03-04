<?php
use Timber\Timber;
$context['options'] = Timber::get_context();
add_action( 'wp_enqueue_scripts', 'search_scripts' );

function search_scripts (){
    wp_enqueue_style('card-post-full', get_template_directory_uri() . '/dist/css/posts_highlight/posts_highlight.css');
    wp_enqueue_style('search', get_template_directory_uri() . '/dist/css/search.css');
}
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => 10,
        's' => $_GET['s'],
        'paged' => $paged
);
query_posts($args);

$context['posts'] = Timber::get_posts($args);
$context['pagination'] = Timber::get_pagination($args);

get_header();

Timber::render('views/pages/search.twig', $context);

get_footer();
