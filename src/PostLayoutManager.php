<?php

namespace Jankx\PostLayout;

use Jankx\Contracts\TemplateEngine\EngineInterface;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

use Jankx\PostLayout\Contracts\PostLayoutParent;
use Jankx\PostLayout\Contracts\PostLayoutChildren;
use Jankx\PostLayout\Request\PostsFetcher;
use Jankx\PostLayout\Layout\ListLayout;
use Jankx\PostLayout\Layout\Card;
use Jankx\PostLayout\Layout\Carousel;
use Jankx\PostLayout\Layout\Grid;
use Jankx\PostLayout\Layout\Tabs;
use Jankx\PostLayout\Layout\Preset1;
use Jankx\PostLayout\Layout\Preset2;
use Jankx\PostLayout\Layout\Preset3;
use Jankx\PostLayout\Layout\Preset4;
use Jankx\PostLayout\Layout\Preset5;
use Jankx\PostLayout\Layout\Preset6;
use Jankx\PostLayout\TermLayout\Card as TermCardLayout;
use Jankx\PostLayout\TermLayout\Carousel as TermCarouselLayout;

class PostLayoutManager
{
    const VERSION = '1.0.3';

    protected static $instances;
    protected static $supportedLayouts;

    protected static $supportedLoopItemLayouts;
    protected static $supportedTermLayouts;

    protected static $assetsDirUrl;

    protected $templateEngine;
    protected $registeredFunctions = false;

    protected static $isBootstrap = false;

    /**
     * @param null|EngineInterface|string $engineId
     *
     * @return static|null
     */
    public static function getInstance($engineId = null) : ? self
    {
        if (is_a($engineId, EngineInterface::class)) {
            $engineId = $engineId->getId();
        }
        if (isset(static::$instances[$engineId])) {
            return static::$instances[$engineId];
        }
        return null;
    }

    /**
     * @return self
     */
    public static function createInstance($templateEngine)
    {
        if (is_null($templateEngine)) {
            throw new \InvalidArgumentException('Template engine cannot be null');
        }

        $id = $templateEngine->getId();
        if (is_null($id)) {
            throw new \InvalidArgumentException('Template engine ID cannot be null');
        }

        error_log("[PostLayoutManager Debug] Creating instance for engine ID: " . $id);
        error_log("[PostLayoutManager Debug] Engine class: " . get_class($templateEngine));

        if (!isset(static::$instances[$id])) {
            error_log("[PostLayoutManager Debug] Creating new PostLayoutManager instance");
            static::$instances[$id] = new static($templateEngine);
            error_log("[PostLayoutManager Debug] PostLayoutManager instance created successfully");
        } else {
            error_log("[PostLayoutManager Debug] Using existing PostLayoutManager instance");
        }

        return static::$instances[$id];
    }

    private function __construct($templateEngine)
    {
        if (is_null($templateEngine)) {
            throw new \InvalidArgumentException('Template engine cannot be null in constructor');
        }

        error_log("[PostLayoutManager Debug] Constructor called");
        error_log("[PostLayoutManager Debug] Template engine class: " . get_class($templateEngine));

        $engineId = $templateEngine->getId();
        if (is_null($engineId)) {
            throw new \InvalidArgumentException('Template engine ID cannot be null in constructor');
        }
        error_log("[PostLayoutManager Debug] Template engine ID: " . $engineId);

        if (empty(static::$instances)) {
            error_log("[PostLayoutManager Debug] First instance - initializing hooks");
            $this->initHooks();

            $fetcher = new PostsFetcher();
            did_action('init')
                ? $fetcher->init()
                : add_action('init', array($fetcher, 'init'));
            error_log("[PostLayoutManager Debug] PostsFetcher initialized");
        }

        $this->templateEngine = &$templateEngine;
        error_log("[PostLayoutManager Debug] Template engine assigned to instance");

        if ($this->templateEngine && !$this->registeredFunctions) {
            error_log("[PostLayoutManager Debug] Registering template functions");
            $this->templateEngine->registerFunction(
                'get_meta_value',
                array(Utils::class, 'get_meta_value')
            );
            $this->registeredFunctions = true;
            error_log("[PostLayoutManager Debug] Template functions registered");
        }

        // Register template directories
        if ($this->templateEngine) {
            error_log("[PostLayoutManager Debug] Registering template directories");

            // Get theme directory
            $themeDir = get_template_directory();
            $viewsDir = $themeDir . '/views/post-layouts';

            error_log("[PostLayoutManager Debug] Theme directory: " . $themeDir);
            error_log("[PostLayoutManager Debug] Views directory: " . $viewsDir);

            if (is_dir($viewsDir)) {
                $this->templateEngine->addTemplateDirectory($viewsDir, 'post-layouts');
                error_log("[PostLayoutManager Debug] Template directory registered: " . $viewsDir);
            } else {
                error_log("[PostLayoutManager Debug] Template directory not found: " . $viewsDir);
            }
        }

        if (!self::$isBootstrap) {
            self::$isBootstrap = true;
            error_log("[PostLayoutManager Debug] Bootstrap flag set");
        }

        error_log("[PostLayoutManager Debug] Constructor completed successfully");
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
                    Preset6::LAYOUT_NAME => Preset6::class,
                ));
            }
        }

        if ((is_null(static::$supportedTermLayouts) && $args['data'] === 'term') || $refresh) {
            static::$supportedTermLayouts = apply_filters('jankx_post_layout_term_layouts', array(
                TermCardLayout::LAYOUT_NAME => TermCardLayout::class,
                TermCarouselLayout::LAYOUT_NAME => TermCarouselLayout::class,
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



    public function getSupportedLoopItemContentLayouts($refresh = false)
    {
        if (is_null(static::$supportedLoopItemLayouts) || $refresh) {
            static::$supportedLoopItemLayouts = apply_filters(
                'jankx/posts/loop/layouts',
                [
                ]
            );
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("[PostLayoutManager Debug] Supported loop item layouts: " . print_r(static::$supportedLoopItemLayouts, true));
            }
        }
        return static::$supportedLoopItemLayouts;
    }

    /**
     * Summary of getLoopItemContentByType
     * @param mixed $type
     *
     * @return \Jankx\PostLayout\Contracts\LoopItemContentInterface | null
     */
    public function getLoopItemContentByType($type)
    {
        if (is_null($type)) {
            throw new \InvalidArgumentException('Loop item content type cannot be null');
        }

        $supportedLayouts = $this->getSupportedLoopItemContentLayouts();
        if (!isset($supportedLayouts[$type]) || !class_exists($supportedLayouts[$type])) {
            throw new \InvalidArgumentException("Loop item content type '{$type}' is not supported or class does not exist");
        }

        $layoutCls = $supportedLayouts[$type];
        return new $layoutCls();
    }

    /**
     * @return \Jankx\PostLayout\PostLayout
     */
    public function createLayout($layoutName, $wp_query = null, $loopItemLayout = null)
    {
        if (is_null($layoutName)) {
            throw new \InvalidArgumentException('Layout name cannot be null');
        }

        if (is_null($this->templateEngine)) {
            throw new \InvalidArgumentException('Template engine is null - cannot create layout');
        }

        $supportedLayouts = static::getLayouts([
            'type' => 'all'
        ]);
        if (empty($supportedLayouts[$layoutName])) {
            throw new \InvalidArgumentException("Layout '{$layoutName}' is not supported");
        }
        $layoutCls = $supportedLayouts[$layoutName];
        $layout    = new $layoutCls($wp_query, $loopItemLayout);

        $layout->setTemplateEngine($this->templateEngine);

        return $layout;
    }

    public function createTermLayout($layoutName, $wp_term_query = null)
    {
        if (is_null($layoutName)) {
            throw new \InvalidArgumentException('Layout name cannot be null');
        }

        if (is_null($this->templateEngine)) {
            throw new \InvalidArgumentException('Template engine is null - cannot create term layout');
        }

        $supportedLayouts = static::getLayouts(array(
            'data' => 'term'
        ));
        if (empty($supportedLayouts[$layoutName])) {
            throw new \InvalidArgumentException("Term layout '{$layoutName}' is not supported");
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
        $assetsDir     = sprintf('%s/assets/', dirname(__DIR__));
        $fslightbox    = $assetsDir . 'libs/fslightbox-basic/fslightbox.js';
        $fslightboxVer = substr(md5(fileatime($fslightbox)), 0, 5);

        // Đăng ký và enqueue CSS trực tiếp bằng WordPress
        wp_register_style(
            'swiffy-slider',
            $this->asset_url('libs/swiffy-slider/css/swiffy-slider.min.css'),
            [],
            '1.6.0',
            'all'
        );
        wp_enqueue_style('swiffy-slider');

        wp_register_style(
            'jankx-post-layout',
            $this->asset_url('css/post-layout.min.css'),
            array('swiffy-slider'),
            static::VERSION,
            'all'
        );
        wp_enqueue_style('jankx-post-layout');

        // Đăng ký và enqueue JS trực tiếp bằng WordPress
        wp_register_script(
            'fslightbox',
            $this->asset_url('libs/fslightbox-basic/fslightbox.js'),
            array(),
            '3.3-' . $fslightboxVer,
            true
        );
        wp_enqueue_script('fslightbox');

        wp_register_script(
            'swiffy-slider',
            $this->asset_url('libs/swiffy-slider/js/swiffy-slider.min.js'),
            array(),
            '1.6.0',
            true
        );
        wp_enqueue_script('swiffy-slider');

        $jsDeps = ['jankx-common', 'swiffy-slider', 'fslightbox'];
        if (Carousel::getDragEnable()) {
            wp_register_script(
                'swiffy-slider-drag',
                $this->asset_url('libs/swiffy-slider/js/swiffy-slider-extensions.min.js'),
                array('swiffy-slider'),
                '1.6.0',
                true
            );
            wp_enqueue_script('swiffy-slider-drag');
            $jsDeps[] = 'swiffy-slider-drag';
        }

        wp_register_script(
            'jankx-post-layout',
            $this->asset_url('js/post-layout.min.js'),
            $jsDeps,
            static::VERSION,
            true
        );
        wp_localize_script(
            'jankx-post-layout',
            'jkx_post_layout',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'action' => PostsFetcher::FETCH_POSTS_ACTION,
            )
        );
        wp_enqueue_script('jankx-post-layout');
    }

    public function postLayoutClasses($classes, $post, $layoutOptions = array())
    {
        if (Utils::array_get($layoutOptions, 'show_thumbnail', true)) {
            $classes[] = 'thumbnail-' . Utils::array_get($layoutOptions, 'thumbnail_position', 'top');
        }
        return $classes;
    }
}
