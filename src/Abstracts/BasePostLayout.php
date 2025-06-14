<?php

namespace Jankx\PostLayout\Abstracts;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

use Jankx\PostLayout\Constracts\LoopItemContentInterface;
use Jankx\PostLayout\Constracts\PostLayout;

abstract class BasePostLayout implements PostLayout
{
    protected $loopItemLayout;

    public function setLoopItemLayout($loopItemLayout)
    {
        if (is_a($loopItemLayout, LoopItemContentInterface::class)) {
            $this->loopItemLayout = $loopItemLayout;
        }
        return $this;
    }

    public function getLoopItemLayout()
    {
        return $this->loopItemLayout;
    }
}
