<?php

namespace Jankx\PostLayout\Layout;

if (!defined('ABSPATH')) {
    exit('Cheatin huh?');
}

use Jankx\PostLayout\Constracts\PostLayoutChildren;
use Jankx\PostLayout\PostLayout;

class ListLayout extends PostLayout implements PostLayoutChildren
{
    const LAYOUT_NAME = 'list';

    protected $supportColumns = false;

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
}
