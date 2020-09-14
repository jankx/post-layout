<?php
namespace Jankx\PostLayout;

use Jankx\PostLayout\Layout\ListLayout;
use Jankx\PostLayout\Layout\LargePostWithList;

class PostLayoutManager
{
    const LIST = 'list';
    const LEFT_POST_WITH_RIGHT_LIST = 'left_post_with_right_list';

    protected static $instance;

    protected $supportedLayouts;

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
        $this->getLayouts();
        $this->loadHelpers();
    }

    public function getLayouts($args = array(), $refresh = false)
    {
        if (is_null($this->supportedLayouts) || $refresh) {
            $this->supportedLayouts = apply_filters('jankx_post_layout_layouts', array(
                static::LIST => array(
                    'name' => __('List Layout', 'jankx'),
                    'class' => ListLayout::class,
                ),
                static::LEFT_POST_WITH_RIGHT_LIST => array(
                    'name' => __('Preset 1', 'jankx'),
                    'class' => LargePostWithList::class,
                )
            ));
        }
        $args = wp_parse_args($args, array(
            'type' => 'all',
        ));

        if ($args['type'] === 'names') {
            return array_map(function ($value) {
                return $value['name'];
            }, $this->supportedLayouts);
        }

        return $this->supportedLayouts;
    }

    public function getLayoutClass($layoutName = 'list')
    {
        if (empty($this->supportedLayouts[$layoutName])) {
            return;
        }

        $layout = $this->supportedLayouts[$layoutName];
        if (is_array($layout)) {
            return array_get($layout, 'class');
        }
        return $layout;
    }

    public function loadHelpers()
    {
        require_once realpath(dirname(__FILE__) . '/../functions.php');
    }
}
