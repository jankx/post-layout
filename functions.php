<?php
if (!function_exists('jankx_post_loop_start')) {
    function jankx_post_loop_start() {
        $loop_start = jankx_template('post/loop/loop-start', array(), 'start_loop_post', false);

        echo apply_filters('jankx_post_loop_start', $loop_start);
    }
}

if (!function_exists('jankx_post_loop_end')) {
    function jankx_post_loop_end() {
        $loop_end = jankx_template('post/loop/loop-end', array(), 'end_loop_post', false);

        echo apply_filters('jankx_post_loop_end', $loop_end);
    }
}

