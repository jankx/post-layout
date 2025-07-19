<?php

namespace Jankx\PostLayout\Layout;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

use Jankx\PostLayout\Contracts\PostLayoutChildren;
use Jankx\PostLayout\PostLayout;

class Grid extends PostLayout implements PostLayoutChildren
{
    const LAYOUT_NAME = 'grid';

    protected $supportColumns = true;

    public function get_name()
    {
        return static::LAYOUT_NAME;
    }

    public static function get_layout_label()
    {
        return __('Grid', 'jankx');
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
