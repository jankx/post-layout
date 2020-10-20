<?php
use Jankx\PostLayout\PostLayoutManager;

function jankx_post_loop_start($list_type = PostLayoutManager::LIST_LAYOUT, $args = null) {
    $postsListClasses = array('jankx-posts');
    $attributes = array(
        'class' => $postsListClasses,
    );

    echo '<div ' . jankx_generate_html_attributes($attributes) . '>';

    // This hook use to start custom render post layout
    do_action('jankx_post_loop_start', $list_type, $args);
}

function jankx_post_loop_end($list_type = null, $args = null) {
    // This hook use to stop custom render post layout
    do_action('jankx_post_loop_end', $list_type, $args);

    // Close posts list wrapper
    echo '</div><!-- End .jankx-posts -->';
}

function jankx_post_layout_wrapper($close = false) {
    $action = $close ? 'close' : 'open';
    do_action("jankx_post_layout_wrapper_{$action}");
}

