<?php

namespace Jankx\PostLayout\Abstracts;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

use Jankx\PostLayout\Constracts\LoopItemContentInterface;

abstract class LoopItemContent implements LoopItemContentInterface
{
    public function getLoopItemCssClass()
    {
        return strtolower(static::getType());
    }

    public function getPostClassPriority()
    {
        return 10;
    }

    /**
     * @return string|null
     */
    public function getPostClassHook(): ?string
    {
        return null;
    }

    public function postLoopItemCssClass($classes)
    {
        if (empty(self::getLoopItemCssClass())) {
            return $classes;
        }

        $classes[] = 'item-' . strtolower(self::getLoopItemCssClass());

        return $classes;
    }
}
