<?php

namespace Jankx\PostLayout\Contracts;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

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
