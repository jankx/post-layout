<?php

namespace Jankx\PostLayout\Contracts;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

interface PostLayout extends LayoutBase
{
    public function render($echo = true);

    public function is_pro();
}
