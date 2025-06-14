<?php

namespace Jankx\PostLayout\Constracts;

if (!defined('ABSPATH')) {
    exit('Cheatin huh?');
}

interface LayoutBase
{
    public static function get_layout_label();

    public function get_name();

    public function setTemplateEngine($templateEngine);

    public function setOptions($options);

    public function getOptions();

    /**
     * @param \Jankx\PostLayout\Constracts\LoopItemContentInterface $loopItemLayout
     *
     * @return self
     */
    public function setLoopItemLayout($loopItemLayout);

    /**
     * Summary of getLoopItemLayout
     *
     * @return \Jankx\PostLayout\Constracts\LoopItemContentInterface
     */
    public function getLoopItemLayout();

    public function getOption($optionName, $defaultValue = null);

    public function addDataProcessor($callback);
}
