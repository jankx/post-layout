<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\Constracts\PostLayoutParent;
use Jankx\PostLayout\PostLayout;

class Tabs extends PostLayout implements PostLayoutParent
{
    const LAYOUT_NAME = 'tabs';

    public function get_name()
    {
        return static::LAYOUT_NAME;
    }

    public static function get_layout_label()
    {
        return __('Tabs', 'jankx');
    }

    public function render($echo = true)
    {
    }
}
