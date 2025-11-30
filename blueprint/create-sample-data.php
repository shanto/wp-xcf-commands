<?php

function xcf_create_sample_services() {
    // Check if posts already exist
    $existing_services = get_posts(array(
        'post_type' => 'services',
        'posts_per_page' => 1,
        'post_status' => 'any'
    ));
    
    if (!empty($existing_services)) {
        return; // Exit if posts already exist
    }

    $services = array(
        array(
            'title' => 'Web Development',
            'content' => 'Professional web development services tailored to your business needs. We create responsive, fast, and secure websites.',
            'excerpt' => 'Custom web development solutions for your business'
        ),
        array(
            'title' => 'SEO Optimization',
            'content' => 'Improve your website\'s visibility with our comprehensive SEO services. We help you rank higher in search results.',
            'excerpt' => 'Boost your online presence with our SEO expertise'
        )
    );

    foreach ($services as $service) {
        wp_insert_post(array(
            'post_title'   => $service['title'],
            'post_content' => $service['content'],
            'post_excerpt' => $service['excerpt'],
            'post_status'  => 'publish',
            'post_type'    => 'services',
        ));
    }
}

function xcf_create_sample_stories() {
    // Check if posts already exist
    $existing_stories = get_posts(array(
        'post_type' => 'stories',
        'posts_per_page' => 1,
        'post_status' => 'any'
    ));
    
    if (!empty($existing_stories)) {
        return; // Exit if posts already exist
    }

    $stories = array(
        array(
            'title' => 'Our Journey to Success',
            'content' => 'Starting from a small team in a garage, we grew to become a leading company in our industry. Our dedication and hard work paid off when we won the Best Startup Award in 2023.',
            'excerpt' => 'How we transformed our small startup into an industry leader'
        ),
        array(
            'title' => 'Customer Success Story: Acme Corp',
            'content' => 'Acme Corp increased their revenue by 200% after implementing our solutions. Hear directly from their team about the challenges they faced and how we helped them overcome these obstacles.',
            'excerpt' => 'See how Acme Corp achieved remarkable growth with our help'
        )
    );

    foreach ($stories as $story) {
        wp_insert_post(array(
            'post_title'   => $story['title'],
            'post_content' => $story['content'],
            'post_excerpt' => $story['excerpt'],
            'post_status'  => 'publish',
            'post_type'    => 'stories',
        ));
    }
}

// Run the functions after WordPress is fully loaded
add_action('init', function() {
    // Only run if we're in the admin or CLI context
    if (!is_blog_installed() || (function_exists('wp_doing_ajax') && wp_doing_ajax())) {
        return;
    }
    
    // Create sample data
    xcf_create_sample_services();
    xcf_create_sample_stories();
}, 20); // Higher priority to ensure post types are registered
