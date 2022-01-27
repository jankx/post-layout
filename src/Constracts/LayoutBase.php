<?php
namespace Jankx\PostLayout\Constracts;

interface LayoutBase
{
    public static function get_layout_label();

    public function get_name();

    public function setTemplateEngine($templateEngine);

    public function setOptions($options);

    public function getOptions();

    public function getOption($optionName, $defaultValue = null);

    public function addDataProcessor($callback);
}
