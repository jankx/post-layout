<?php

namespace Jankx\PostLayout\Constracts;

interface LoopItemContentInterface
{
    public static function getType();

    public function contentStart();
    public function contentEnd();

    public function getLoopItemCssClass();
}
