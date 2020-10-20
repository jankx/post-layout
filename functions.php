<?php
use Jankx\PostLayout\PostLayoutManager;

function jankx_post_loop_start($listStyle = PostLayoutManager::LIST_LAYOUT, $args = array()) {
    $postsListClasses = array('jankx-posts');
    if (strpos($listStyle, 'layout') === false) {
        $postsListClasses[] = sprintf('%s_layout', $listStyle);
    }
    if (isset($args['columns'])) {
        $postsListClasses[] = 'columns-' . $args['columns'];
    }
    $attributes = array(
        'class' => $postsListClasses,
    );

    echo '<div ' . jankx_generate_html_attributes($attributes) . '>';

    // This hook use to start custom render post layout
    do_action('jankx_post_loop_start', $listStyle, $args);
}

function jankx_post_loop_end($listStyle = null, $args = null) {
    // This hook use to stop custom render post layout
    do_action('jankx_post_loop_end', $listStyle, $args);

    // Close posts list wrapper
    echo '</div><!-- End .jankx-posts -->';
}

function jankx_post_layout_wrapper($close = false) {
    $action = $close ? 'close' : 'open';
    do_action("jankx_post_layout_wrapper_{$action}");
}

