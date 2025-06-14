<?php

namespace Jankx\PostLayout\TermLayout;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

use Jankx\PostLayout\TermLayout;

class Carousel extends TermLayout
{
    const LAYOUT_NAME = 'carousel';

    protected $supportColumns = true;

    public static function get_layout_label()
    {
        return __('Carousel', 'jankx');
    }

    public function get_name()
    {
        return static::LAYOUT_NAME;
    }
}
