<?php

namespace Jankx\PostLayout\Constracts;

interface LayoutBase
{
    public static function get_layout_label();

    public function get_name();

    public function setTemplateEngine($templateEngine);

    public function setOptions($options);

    public function getOptions();

    /**
     * @param \Jankx\PostLayout\Constracts\LoopItemLayoutInterface $loopItemLayout
     *
     * @return self
     */
    public function setLoopItemLayout($loopItemLayout);

    /**
     * Summary of getLoopItemLayout
     *
     * @return \Jankx\PostLayout\Constracts\LoopItemLayoutInterface
     */
    public function getLoopItemLayout();

    public function getOption($optionName, $defaultValue = null);

    public function addDataProcessor($callback);
}
