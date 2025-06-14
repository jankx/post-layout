<?php

namespace Jankx\PostLayout\TermLayout;

if (!defined('ABSPATH')) {
    exit('Cheatin huh?');
}

use Jankx\PostLayout\TermLayout;

class Card extends TermLayout
{
    const LAYOUT_NAME = 'card';

    protected $supportColumns = true;

    public static function get_layout_label()
    {
        return __('Card', 'jankx');
    }

    public function get_name()
    {
        return static::LAYOUT_NAME;
    }
}
