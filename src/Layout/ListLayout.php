<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\PostLayout;

class ListLayout extends PostLayout
{
    const LAYOUT_NAME = 'list';

    public function get_name()
    {
        return 'list';
    }

    public static function get_layout_label()
    {
        return __('List', 'jankx');
    }

    protected function defaultOptions()
    {
        return array(
            'large_first_post' => false,
            'show_thumbnail' => true,
            'thumbnail_position' => 'left',
            'header_text' => '',
            'show_excerpt' => false,
        );
    }

    protected function createCustomPostClass(&$post = null)
    {
        if (is_a($post, \WP_Post::class)) {
            $classes = array();
            if ($this->options['show_thumbnail']) {
                $classes[] = 'thumbnail-' . $this->options['thumbnail_position'];
            }
            $post->custom_post_class = $classes;
        }
    }
}
