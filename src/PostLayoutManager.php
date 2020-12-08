<?php
namespace Jankx\PostLayout;

use Jankx\PostLayout\Layout\ListLayout;
use Jankx\PostLayout\Layout\Preset1;
use Jankx\PostLayout\Layout\Mansory;
use Jankx\PostLayout\Layout\Card;
use Jankx\PostLayout\Layout\Carousel;
use Jankx\PostLayout\Layout\Grid;

class PostLayoutManager
{
    const LIST_LAYOUT = 'list';
    const PRESET_1 = 'left_post_with_right_list';
    const MANSORY = 'mansory';
    const CARD = 'card';
    const CAROUSEL = 'carousel';
    const GRID = 'grid';

    protected static $instance;

    protected $supportedLayouts;
    protected $templateLoader;


    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
        $this->templateLoader = new PostTemplateLoader();

        $this->getLayouts();
        $this->initHooks();
    }

    public function getLayouts($args = array(), $refresh = false)
    {
        if (is_null($this->supportedLayouts) || $refresh) {
            $this->supportedLayouts = apply_filters('jankx_post_layout_layouts', array(
                static::LIST_LAYOUT => array(
                    'name' => __('List', 'jankx'),
                    'class' => ListLayout::class,
                ),
                static::PRESET_1 => array(
                    'name' => __('Preset 1', 'jankx'),
                    'class' => Preset1::class,
                ),
                static::CARD => array(
                    'name' => __('Card', 'jankx'),
                    'class' => Card::class,
                ),
                static::CAROUSEL => array(
                    'name' => __('Carousel', 'jankx'),
                    'class' => Carousel::class,
                ),
                static::GRID => array(
                    'name' => __('Grid', 'jankx'),
                    'class' => Grid::class,
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

    public function initHooks()
    {
        add_action('template_redirect', array($this->templateLoader, 'load'));
        add_filter('post_class', array(PostLayout::class, 'postClasses'));
    }
}
