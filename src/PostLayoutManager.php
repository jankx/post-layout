<?php
namespace Jankx\PostLayout;

use Jankx\TemplateEngine\Engine;
use Jankx\PostLayout\Constracts\PostLayoutParent;
use Jankx\PostLayout\Constracts\PostLayoutChildren;
use Jankx\PostLayout\Request\PostsFetcher;
use Jankx\PostLayout\Layout\ListLayout;
use Jankx\PostLayout\Layout\Mansory;
use Jankx\PostLayout\Layout\Card;
use Jankx\PostLayout\Layout\Carousel;
use Jankx\PostLayout\Layout\Grid;
use Jankx\PostLayout\Layout\Tabs;
use Jankx\PostLayout\Layout\Preset1;
use Jankx\PostLayout\Layout\Preset2;
use Jankx\PostLayout\Layout\Preset3;
use Jankx\PostLayout\Layout\Preset4;
use Jankx\PostLayout\Layout\Preset5;

use Jankx\PostLayout\TermLayout\Card as TermCardLayout;

class PostLayoutManager
{
    const VERSION = '1.0.114';

    protected static $instances;
    protected static $supportedLayouts;
    protected static $supportedTermLayouts;

    protected static $assetsDirUrl;

    protected $templateEngine;

    public static function getInstance($engineId = null)
    {
        if (is_a($engineId, Engine::class)) {
            $engineId = $engineId->getId();
        }
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

            $fetcher = new PostsFetcher();
            did_action('init')
                ? $fetcher->init()
                : add_action('init', array($fetcher, 'init'));
        }
        $this->templateEngine = &$templateEngine;

        if ($this->templateEngine) {
            $this->templateEngine->registerFunction(
                'get_meta_value',
                array(PostLayout::class, 'get_meta_value')
            );
        }
    }


    public static function getLayouts($args = array(), $refresh = false)
    {
        $args = wp_parse_args($args, array(
            'data' => 'post',
            'field' => 'all',
            'type' => '',
        ));

        if ((is_null(static::$supportedLayouts) && $args['data'] !== 'term') || $refresh) {
            static::$supportedLayouts = apply_filters('jankx_post_layout_layouts', array(
                ListLayout::LAYOUT_NAME => ListLayout::class,
                Card::LAYOUT_NAME => Card::class,
                Carousel::LAYOUT_NAME => Carousel::class,
                Grid::LAYOUT_NAME => Grid::class,
                Tabs::LAYOUT_NAME => Tabs::class,
            ));

            // Support PRO layouts
            if (true) {
                static::$supportedLayouts = array_merge(static::$supportedLayouts, array(
                    Preset1::LAYOUT_NAME => Preset1::class,
                    Preset2::LAYOUT_NAME => Preset2::class,
                    Preset3::LAYOUT_NAME => Preset3::class,
                    Preset4::LAYOUT_NAME => Preset4::class,
                    Preset5::LAYOUT_NAME => Preset5::class,
                ));
            }
        }

        if ((is_null(static::$supportedTermLayouts) && $args['data'] === 'term') || $refresh) {
            static::$supportedTermLayouts = apply_filters('jankx_post_layout_term_layouts', array(
                TermCardLayout::LAYOUT_NAME => TermCardLayout::class,
            ));
        }

        $ret = $args['data'] !== 'term' ? static::$supportedLayouts : static::$supportedTermLayouts;

        if ($args['type'] !== 'all') {
            switch ($args['type']) {
                case 'child':
                case 'children':
                    $ret = array_filter($ret, function ($layoutCls) {
                        return is_a($layoutCls, PostLayoutChildren::class, true);
                    });
                    break;
                case 'parent':
                    $ret = array_filter($ret, function ($layoutCls) {
                        return is_a($layoutCls, PostLayoutParent::class, true);
                    });
                    break;
                default:
                    $ret = array_filter($ret, function ($layoutCls) {
                        return !is_a($layoutCls, PostLayoutParent::class, true);
                    });
            }
        }

        if (isset($args['exclude'])) {
            if ($args['exclude'] === 'parent') {
                $ret = array_filter($ret, function ($layoutCls) {
                    return !is_a($layoutCls, PostLayoutParent::class, true);
                });
            } elseif (in_array($args['exclude'], array('child', 'children'))) {
                $ret = array_filter($ret, function ($layoutCls) {
                    return !is_a($layoutCls, PostLayoutParent::class, true);
                });
            }
        }

        if ($args['field'] === 'names') {
            $names = array_map(function ($layoutCls) {
                return $layoutCls::get_layout_label();
            }, $ret);

            return $names;
        }

        if ($args['field'] === 'keys') {
            return array_keys($ret);
        }

        return $ret;
    }

    public function createLayout($layoutName, $wp_query = null)
    {
        $supportedLayouts = static::getLayouts([
            'type' => 'all'
        ]);
        if (empty($supportedLayouts[$layoutName])) {
            return;
        }
        $layoutCls = $supportedLayouts[$layoutName];
        $layout    = new $layoutCls($wp_query);

        $layout->setTemplateEngine($this->templateEngine);

        return $layout;
    }

    public function createTermLayout($layoutName, $wp_term_query = null)
    {
        $supportedLayouts = static::getLayouts(array(
            'data' => 'term'
        ));
        if (empty($supportedLayouts[$layoutName])) {
            return;
        }
        $layoutCls = $supportedLayouts[$layoutName];
        $layout    = new $layoutCls($wp_term_query);

        $layout->setTemplateEngine($this->templateEngine);

        return $layout;
    }

    public function initHooks()
    {
        add_filter('jankx/layout/post/item/classes', array($this, 'postLayoutClasses'), 10, 3);
        add_action('wp', array($this, 'registerScripts'), 15);
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
        $splideCss = apply_filters('jankx_post_layout_use_splide_core_css', false)
            ? 'splide-core.min'
            : 'splide.min';
        css(
            'splide',
            $this->asset_url("libs/splide/css/{$splideCss}.css"),
            array(),
            '2.4.12'
        );

        css(
            'jankx-post-layout',
            $this->asset_url('css/post-layout.css'),
            array('splide'),
            static::VERSION
        );

        js(
            'fslightbox',
            $this->asset_url('libs/fslightbox-basic/fslightbox.js'),
            array(),
            '3.3',
            true
        );

        js(
            'splide',
            $this->asset_url('libs/splide/js/splide.js'),
            array(),
            '2.4.12',
            true
        );

        js(
            'jankx-post-layout',
            $this->asset_url('js/post-layout.js'),
            array('jankx-common', 'splide', 'fslightbox'),
            static::VERSION,
            true
        )
            ->localize('jkx_post_layout', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'action' => PostsFetcher::FETCH_POSTS_ACTION,
            ));

        if (defined('JANKX_FRAMEWORK_FILE_LOADER')) {
            add_filter('jankx_asset_css_dependences', function ($deps) {
                array_push($deps, 'jankx-post-layout');
                return $deps;
            });

            add_filter('jankx_asset_js_dependences', function ($deps) {
                array_push($deps, 'jankx-post-layout');
                return $deps;
            });
        } else {
            css('jankx-post-layout');
            js('jankx-post-layout');
        }
    }

    public function postLayoutClasses($classes, $post, $layoutOptions = array())
    {
        if ($layoutOptions['show_thumbnail']) {
            $classes[] = 'thumbnail-' . $layoutOptions['thumbnail_position'];
        }
        return $classes;
    }
}
