<?php

namespace Jankx\PostLayout\Constracts;

interface PostLayout extends LayoutBase
{
    public function render($echo = true);

    public function is_pro();
}
