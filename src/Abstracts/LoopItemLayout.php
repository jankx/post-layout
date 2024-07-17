<?php

namespace Jankx\PostLayout\Abstracts;

use Jankx\PostLayout\Constracts\LoopItemLayoutInterface;

abstract class LoopItemLayout implements LoopItemLayoutInterface
{
    public function getLoopItemCssClass()
    {
        return 'item-' . strtolower(self::getLoopItemCssClass());
    }
}
