<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\Constracts\PostLayoutParent;
use Jankx\PostLayout\PostLayout;

class Tabs extends PostLayout implements PostLayoutParent
{
    const LAYOUT_NAME = 'tabs';

    protected $tabs = array();

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

    public function addTab($tab_title, $tab_object, $url = null)
    {
        $tab_object = wp_parse_args($tab_object, array(
            'type' => '', // WordPress data type such as: post, taxonomy
            'name' => '', // The type of WordPress data type such as: post, page, category, etc,...
            'id' => '', // The object ID
        ));
    }

    public function addTabs($tabs)
    {
        foreach ($tabs as $tab) {
            if (!isset($tab['title'], $tab['object'])) {
                error_log(sprintf('Jankx post layout has skipped tab: %s', json_encode($tab)));
                continue;
            }
            $this->addTab($tab['title'], $tab['object'], array_get($tab, 'url'));
        }
    }
}
