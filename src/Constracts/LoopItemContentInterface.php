<?php

namespace Jankx\PostLayout\Constracts;

interface LoopItemContentInterface
{
    public static function getType();

    public function contentStart();
    public function contentEnd();

    public function getPostClassHook();

    public function getPostClassPriority();

    public function postLoopItemCssClass($classes);

    public function getLoopItemCssClass();
}
