<?php

namespace Jankx\PostLayout\Layout;

if (!defined('ABSPATH')) {
    exit('Cheatin huh?');
}

use Jankx\PostLayout\Constracts\PostLayoutChildren;
use Jankx\PostLayout\PostLayout;

class Card extends PostLayout implements PostLayoutChildren
{
    const LAYOUT_NAME = 'card';

    protected $supportColumns = true;

    public function get_name()
    {
        return static::LAYOUT_NAME;
    }

    public static function get_layout_label()
    {
        return __('Card', 'jankx');
    }

    protected function defaultOptions()
    {
        return array(
            'show_thumbnail' => true,
            'thumbnail_position' => 'top',
            'header_text' => '',
            'show_excerpt' => false,
        );
    }
}
