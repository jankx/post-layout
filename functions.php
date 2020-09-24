<?php
if (!function_exists('jankx_post_loop_start')) {
    function jankx_post_loop_start($list_type = 'normal') {
        $loop_start = jankx_template(
            'post/loop-start',
            compact('list_type'),
            'start_loop_post',
            false
        );
        echo apply_filters('jankx_post_loop_start', $loop_start, $list_type);
    }
}

if (!function_exists('jankx_post_loop_end')) {
    function jankx_post_loop_end() {
        $loop_end = jankx_template('post/loop-end', array(), 'end_loop_post', false);

        echo apply_filters('jankx_post_loop_end', $loop_end);
    }
}

