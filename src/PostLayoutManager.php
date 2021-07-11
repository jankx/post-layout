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
    const VERSION = '1.0.0';

    protected static $instance;

    protected $supportedLayouts;
    protected $templateLoader;
    protected $assetsDirUrl;

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
        add_action('wp_enqueue_scripts', array($this, 'registerScripts'));
    }

    public function asset_url($path = '')
    {
        if (is_null($this->assetsDirUrl)) {
            $this->assetsDirUrl = jankx_get_path_url( dirname(__DIR__) );
        }
        return sprintf('%s/assets/%s', $this->assetsDirUrl, $path);
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
