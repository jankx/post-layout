<?php

namespace Jankx\PostLayout\Contracts;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

interface LayoutBase
{
    public static function get_layout_label();

    public function get_name();

    public function setTemplateEngine($templateEngine);

    public function setOptions($options);

    public function getOptions();

    /**
     * @param \Jankx\PostLayout\Contracts\LoopItemContentInterface $loopItemLayout
     *
     * @return self
     */
    public function setLoopItemLayout($loopItemLayout);

    /**
     * Summary of getLoopItemLayout
     *
     * @return \Jankx\PostLayout\Contracts\LoopItemContentInterface
     */
    public function getLoopItemLayout();

    public function getOption($optionName, $defaultValue = null);

    public function addDataProcessor($callback);
}
