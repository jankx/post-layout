<?php

namespace Jankx\PostLayout\Layout;

if (!defined('ABSPATH')) {
    exit('Cheatin huh?');
}

use Jankx\PostLayout\PostLayout;
use Jankx\PostLayout\Constracts\PostLayoutChildren;

class Preset1 extends PostLayout implements PostLayoutChildren
{
    const LAYOUT_NAME = 'preset-1';

    protected $supportColumns = false;

    public function get_name()
    {
        return static::LAYOUT_NAME;
    }

    public static function get_layout_label()
    {
        return sprintf(__('Preset %d', 'jankx'), 1);
    }

    protected function defaultOptions()
    {
        return array(
            'show_thumbnail' => true,
            'thumbnail_position' => 'left',
            'show_excerpt' => false,
        );
    }

    public function is_pro()
    {
        return false;
    }
}
