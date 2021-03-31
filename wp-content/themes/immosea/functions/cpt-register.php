<?php

function register_custom_post_types() {
    /**
     * Post Type: FAQ.
     */
    $labels = array(
        'name'               => _x( 'FAQ', 'post type general name', 'textdomain' ),
        'singular_name'      => _x( 'FAQ', 'post type singular name', 'textdomain' ),
        'menu_name'          => _x( 'FAQ', 'admin menu', 'textdomain' ),
        'name_admin_bar'     => _x( 'FAQ', 'add new on admin bar', 'textdomain' ),
        'add_new'            => _x( 'Add New', 'Dienst', 'textdomain' ),
        'add_new_item'       => __( 'Add New FAQ', 'textdomain' ),
        'new_item'           => __( 'New FAQ', 'textdomain' ),
        'edit_item'          => __( 'Add FAQ', 'textdomain' ),
        'view_item'          => __( 'View FAQ', 'textdomain' ),
        'all_items'          => __( 'All FAQs', 'textdomain' ),
        'search_items'       => __( 'Search FAQ', 'textdomain' ),
        'parent_item_colon'  => __( 'Parent FAQ:', 'textdomain' ),
        'not_found'          => __( 'No FAQ found.', 'textdomain' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'faq' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'menu_icon'          => 'dashicons-admin-comments',
        'supports'           => array( 'title', 'editor' ),
    );
    register_post_type( 'faq', $args );
}

add_action('init', 'register_custom_post_types');
