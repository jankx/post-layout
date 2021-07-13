<?php
namespace Jankx\PostLayout;

use Jankx\PostLayout\Constracts\PostLayoutParent;
use Jankx\PostLayout\Constracts\PostLayoutChildren;

use Jankx\PostLayout\Layout\ListLayout;
use Jankx\PostLayout\Layout\Preset1;
use Jankx\PostLayout\Layout\Mansory;
use Jankx\PostLayout\Layout\Card;
use Jankx\PostLayout\Layout\Carousel;
use Jankx\PostLayout\Layout\Grid;
use Jankx\PostLayout\Layout\Tabs;

class PostLayoutManager
{
    const VERSION = '1.0.0';

    protected static $instances;
    protected static $supportedLayouts;

    protected static $assetsDirUrl;

    protected $templateEngine;

    public static function getInstance($engineId = null)
    {
        if (isset(static::$instances[$engineId])) {
            return static::$instances[$engineId];
        }
    }

    public static function createInstance($templateEngine)
    {
        $id = $templateEngine->getId();
        if (!isset($instances[$id])) {
            static::$instances[$id] = new static($templateEngine);
        }
        return static::$instances[$id];
    }

    private function __construct($templateEngine)
    {
        if (empty(static::$instances)) {
            $this->initHooks();
        }
        $this->templateEngine = &$templateEngine;
    }

    public static function getLayouts($args = array(), $refresh = false)
    {
        if (is_null(static::$supportedLayouts) || $refresh) {
            static::$supportedLayouts = apply_filters('jankx_post_layout_layouts', array(
                ListLayout::LAYOUT_NAME => array(
                    'name' => ListLayout::get_layout_label(),
                    'class' => ListLayout::class,
                ),
                Preset1::LAYOUT_NAME => array(
                    'name' => Preset1::get_layout_label(),
                    'class' => Preset1::class,
                ),
                Card::LAYOUT_NAME => array(
                    'name' => Card::get_layout_label(),
                    'class' => Card::class,
                ),
                Carousel::LAYOUT_NAME => array(
                    'name' => Carousel::get_layout_label(),
                    'class' => Carousel::class,
                ),
                Grid::LAYOUT_NAME => array(
                    'name' => Grid::get_layout_label(),
                    'class' => Grid::class,
                ),
                Tabs::LAYOUT_NAME => array(
                    'name' => Tabs::get_layout_label(),
                    'class' => Tabs::class,
                )
            ));
        }

        $args = wp_parse_args($args, array(
            'field' => 'all',
            'type' => 'all',
        ));
        $ret = static::$supportedLayouts;

        if ($args['type'] !== 'all') {
            switch ($args['type']) {
                case 'child':
                case 'children':
                    $ret = array_filter($ret, function ($value) {
                        return is_a($value['class'], PostLayoutChildren::class, true);
                    });
                    break;
                case 'parent':
                    $ret = array_filter($ret, function ($value) {
                        return is_a($value['class'], PostLayoutParent::class, true);
                    });
                    break;
            }
        }

        if ($args['field'] === 'names') {
            return array_map(function ($value) {
                return $value['name'];
            }, $ret);
        }

        if($args['field'] === 'keys') {
            return array_keys($ret);
        }
    }

    public function createLayout($layoutName, $wp_query = null)
    {
        if (empty(static::$supportedLayouts[$layoutName])) {
            return;
        }
        $layoutCls = array_get(static::$supportedLayouts[$layoutName], 'class');
        $layout    = new $layoutCls($wp_query);

        $layout->setTemplateEngine($this->templateEngine);

        return $layout;
    }

    public function initHooks()
    {
        add_filter('post_class', array(PostLayout::class, 'postClasses'));
        add_action('wp_enqueue_scripts', array($this, 'registerScripts'));
    }

    public function asset_url($path = '')
    {
        if (is_null(static::$assetsDirUrl)) {
            static::$assetsDirUrl = jankx_get_path_url(dirname(__DIR__));
        }
        return sprintf('%s/assets/%s', static::$assetsDirUrl, $path);
    }

    public function registerScripts()
    {
        wp_register_style(
            'jankx-post-layout',
            $this->asset_url('css/post-layout.css'),
            array(),
            static::VERSION
        );

        if (defined('JANKX_FRAMEWORK_FILE_LOADER')) {
            add_filter('jankx_asset_css_dependences', function ($deps) {
                array_push($deps, 'jankx-post-layout');

                return $deps;
            });
        } else {
            wp_enqueue_style('jankx-post-layout');
        }
    }
}
