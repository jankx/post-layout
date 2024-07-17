<?php

namespace Jankx\PostLayout\Constracts;

interface LoopItemLayoutInterface
{
    public static function getType();

    public function loopStart();
    public function loopEnd();

    public function getLoopItemCssClass();
}
