<?php

namespace Jankx\PostLayout\Abstracts;

use Jankx\PostLayout\Constracts\LoopItemContentInterface;

abstract class LoopItemContent implements LoopItemContentInterface
{
    public function getLoopItemCssClass()
    {
        return 'item-' . strtolower(self::getLoopItemCssClass());
    }
}
