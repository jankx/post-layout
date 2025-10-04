<?php

namespace Jankx\PostLayout\LoopItemContent;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

use Jankx\PostLayout\Abstracts\LoopItemContent;

/**
 * Default Loop Item Content
 *
 * @package Jankx\PostLayout\LoopItemContent
 * @since 1.0.0
 */
class DefaultContent extends LoopItemContent
{
    /**
     * Get the type of this loop item content
     *
     * @return string
     */
    public static function getType()
    {
        return 'default';
    }

    /**
     * Content start HTML
     *
     * @return string
     */
    public function contentStart()
    {
        return '<div class="post-content">';
    }

    /**
     * Content end HTML
     *
     * @return string
     */
    public function contentEnd()
    {
        return '</div>';
    }
}
