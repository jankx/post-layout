<?php
namespace Jankx\PostLayout\Constracts;

interface PostLayout
{
    public static function get_layout_label();

    public function render($echo = true);
}
