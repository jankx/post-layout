<?php
namespace Jankx\PostLayout;

use Jankx\PostLayout\Layout\ListLayout;

class PostLayoutManager
{
    const LIST = 'list';

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
    }

    public function getLayouts($args = array(), $refresh = false)
    {
        if (is_null($this->supportedLayouts) || $refresh) {
            $this->supportedLayouts = apply_filters('jankx_post_layout_layouts', array(
                static::LIST => array(
                    'name' => __('List Layout', 'jankx'),
                    'class' => ListLayout::class,
                ),
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

    public function getLayout($layoutName = 'list')
    {
        if (empty($this->supportedLayouts[$layoutName])) {
            return;
        }
        return $this->supportedLayouts[$layoutName];
    }
}
