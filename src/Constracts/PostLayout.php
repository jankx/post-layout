<?php
namespace Jankx\PostLayout\Constracts;

interface PostLayout extends LayoutBase
{
    public function render($echo = true);
}
