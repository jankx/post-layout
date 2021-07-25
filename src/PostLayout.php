<?php
namespace Jankx\PostLayout;

use WP_Query;
use Jankx\Template\Template;
use Jankx\TemplateEngine\Engine;
use Jankx\PostLayout\Constracts\PostLayoutParent;
use Jankx\PostLayout\Constracts\PostLayout as PostLayoutConstract;
use Jankx\PostLayout\PostLayoutManager;

use function wp_parse_args;

abstract class PostLayout implements PostLayoutConstract
{
    const MODE_APPEND = 'append';
    const MODE_REPLACE = 'replace';

    protected static $layoutInstances = array();
    protected static $isElementor = false;
    protected static $customDataFields = array();

    protected $instanceId;
    protected $wp_query;
    protected $templateEngine;
    protected $childLayout;
    protected $hasChildren;
    protected $isContentOnly;

    protected $options = array();
    protected $supportColumns = false;
    protected $contentGenerator;
    protected $contentGeneratorArgs = array();
    protected $contentWrapperTag = false;
    protected $mode = 'replace';


    public function __construct($wp_query = null)
    {
        if (is_null($wp_query)) {
            $this->wp_query = $GLOBALS['wp_query'];
        } elseif (is_a($wp_query, WP_Query::class)) {
            $this->wp_query = $wp_query;
        } else {
            throw new \Exception(sprintf(
                'The query must is an instance of %s',
                WP_Query::class
            ));
        }
        static::$isElementor = ! empty($_REQUEST['action']) && 'elementor' === $_REQUEST['action'] && is_admin();
        if (empty(static::$layoutInstances)) {
            $this->instanceId = 1;
        } else {
            $this->instanceId = max(static::$layoutInstances) + 1;
        }
        array_push(static::$layoutInstances, $this->instanceId);

        $this->options     = $this->defaultOptions();
        $this->hasChildren = is_a($this, PostLayoutParent::class);
    }

    public function setTemplateEngine($engine)
    {
        if (is_a($engine, Engine::class)) {
            $this->templateEngine = $engine;
        }
    }

    public function getId()
    {
        return $this->instanceId;
    }

    abstract public function get_name();

    protected function defaultOptions()
    {
        return array(
            'max_loop_items' => -1,
        );
    }

    public function setOptions($options)
    {
        // Parse post layout with default options
        $this->options =  apply_filters(
            "jankx_post_layout_{$this::get_name()}_set_options",
            wp_parse_args(
                $options,
                $this->options
            ),
            $this
        );
    }

    public function getOptions()
    {
        return (array)$this->options;
    }

    public function disableLoopStartLoopEnd()
    {
        return $this->isContentOnly = true;
    }

    public function setContentWrapperTag($tag)
    {
        $this->contentWrapperTag = $tag;
    }

    protected function checkNextPost()
    {
        $max_loop_items = array_get($this->options, 'max_loop_items');
        if ($max_loop_items > 0) {
            if ($this->wp_query->post_count < $max_loop_items) {
                $max_loop_items = $this->wp_query->post_count;
            }
            return $this->wp_query->current_post < ($max_loop_items - 1);
        }

        return $this->wp_query->have_posts();
    }

    public function loop_start($disableWTopWrapper = false)
    {
        if ($this->isContentOnly) {
            return;
        }
        if (!$disableWTopWrapper) {
            echo '<div ' . jankx_generate_html_attributes($this->createWrapAttributes()) . '>';
        }

        $post_types = (array)$this->wp_query->query_vars['post_type'];
        $postsListClasses = array_merge(
            array('jankx-posts', sprintf('%s-layout', $this->get_name())),
            array_map(function ($post_type) {
                return 'post-type-' . $post_type;
            }, $post_types)
        );

        if ($this->supportColumns && !empty($this->options['columns'])) {
            $postsListClasses[] = 'columns-' . $this->options['columns'];
        }

        $attributes = array(
            'class' => $postsListClasses,
            'data-mode' => $this->mode,
        );

        if ($this->contentWrapperTag) {
            $attributes['data-content-wrapper'] = $this->contentWrapperTag;
        }

        echo '<div ' . jankx_generate_html_attributes($attributes) . '>';

        if (!$this->hasChildren) {
            foreach ($post_types as $post_type) {
                // This hook use to start custom render post layout
                do_action("jankx/layout/{$post_type}/loop/start", $this->get_name(), $this);
            }
        }
    }

    public function loop_end($disableWTopWrapper = false)
    {
        if ($this->isContentOnly) {
            return;
        }
        if (!$this->hasChildren) {
            foreach ((array)$this->wp_query->query_vars['post_type'] as $post_type) {
                // This hook use to stop custom render post layout
                do_action("jankx/layout/{$post_type}/loop/end", $this->get_name(), $this);
            }
        }

            // Close posts list wrapper
        echo '</div><!-- End .jankx-posts -->';
        if (!$disableWTopWrapper) {
            echo '</div><!-- End .jankx-post-layout-wrap -->';
        }
    }

    protected function createCustomPostClass(&$post = null)
    {
    }

    public static function postClasses($classes)
    {
        global $post;
        if (isset($post->custom_post_class)) {
            $classes[] = is_array($post->custom_post_class)
                ? implode(' ', $post->custom_post_class)
                : $post->custom_post_class;
        }
        return $classes;
    }

    protected static function parse_internal_meta($feature, $default_value = false)
    {
        switch ($feature) {
            case 'post_date':
                return get_the_date(get_option('date_format'));
            default:
                return $default_value;
        }
    }

    public static function get_meta_value($value, $feature)
    {
        $func = false;
        $args = array();

        if (isset($value['function']) && is_callable($value['function'])) {
            $func = $value['function'];
            if (isset($value['args'])) {
                $args = $value['args'];
            }
        } elseif (is_callable($value)) {
            $func = $value;
        } else {
            return static::parse_internal_meta($feature, $value);
        }
        if ($func !== false) {
            return call_user_func_array(
                $func,
                $args
            );
        }
    }

    protected function prepareTemplateData($data = array())
    {
        $templateData = wp_parse_args(
            $data,
            array(
                'show_title'         => array_get($this->options, 'show_title', true),
                'show_excerpt'       => array_get($this->options, 'show_excerpt', false),
                'show_thumbnail'     => array_get($this->options, 'show_thumbnail', true),
                'thumbnail_size'     => array_get($this->options, 'thumbnail_size', 'thumbnail'),
                'post_meta_features' => array_get($this->options, 'post_meta_features', array()),
            )
        );

        foreach (static::$customDataFields as $field => $defaultValue) {
            if (!isset($this->options[$field])) {
                $this->options[$field] = $defaultValue;
            }
            $templateData[$field] = $this->options[$field];
        }

        return apply_filters(
            'jankx_post_layout_parse_item_data',
            $templateData,
            $this->wp_query->post,
            $this
        );
    }

    public static function addCustomDataField($fieldName, $defaultValue = null)
    {
        static::$customDataFields[$fieldName] = $defaultValue;
    }

    public function setContentGenerator($generator)
    {
        if (is_callable($generator)) {
            $this->contentGenerator = $generator;
        } elseif (is_array($generator) && isset($generator['function']) && is_callable($generator['function'])) {
            $this->contentGenerator = $generator['function'];
            if (isset($generator['args'])) {
                $this->contentGeneratorArgs = $generator['args'];
            }
        }
    }

    public function the_post()
    {
        $this->wp_query->the_post();
    }

    public function getCurrentPostItem()
    {
        return $this->wp_query->post;
    }

    public function renderLoopItem($post)
    {
        // Setup the post classes
        $this->createCustomPostClass($post);

        if (is_null($this->contentGenerator)) {
            return $this->templateEngine->render(
                array(
                    $post->post_type . '-layout/carousel/loop-item',
                    "post-layout/{$this->get_name()}/loop-item",
                    'post-layout/loop-item'
                ),
                $this->prepareTemplateData()
            );
        }

        $args = $this->contentGeneratorArgs;
        array_push($args, $post);

        return call_user_func_array($this->contentGenerator, $args);
    }

    protected function createWrapAttributes()
    {
        $attributes = array(
            'class' => array('jankx-post-layout-wrap')
        );

        if (!$this->hasChildren) {
            $attributes['data-post-type'] = $this->wp_query->get('post_type');
            $attributes['data-posts-per-page'] = $this->wp_query->get('posts_per_page');
            $attributes['data-layout'] = $this->get_name();
            $attributes['data-engine-id'] = $this->templateEngine->getId();
        }
        return $attributes;
    }

    public function render($echo = true)
    {
        if (!$this->templateEngine) {
            error_log(__('The template engine is not setted to render content', 'jankx_post_layout'));
            return;
        }

        if (!$echo) {
            ob_start();
        }
        foreach ((array)$this->wp_query->query_vars['post_type'] as $post_type) {
            // This hook use to stop custom render post layout
            do_action("jankx/layout/{$post_type}/loop/init", $this->get_name(), $this);
        }
        ?>
            <?php
            // Create post list
            $this->loop_start();

            while ($this->checkNextPost()) {
                $this->the_post();
                $post = $this->getCurrentPostItem();

                do_action('jankx/layout/post/loop/item/before', $post, $this->wp_query, $this);
                $this->renderLoopItem($post);
                do_action('jankx/layout/post/loop/item/after', $post, $this->wp_query, $this);
            }

            $this->loop_end();

            wp_reset_postdata();
            ?>

            <?php if (array_get($this->options, 'show_paginate', false)) : ?>
                <?php echo jankx_paginate(); ?>
            <?php endif; ?>

        <?php
        wp_reset_postdata();
        if (!$echo) {
            return ob_get_clean();
        }
    }

    public function addChildLayout($layoutName)
    {
        if (!is_a($this, PostLayoutParent::class)) {
            throw new \Exception(sprintf(
                '%s post layout is not support add child layout'
            ));
        }
        $postLayoutManager = PostLayoutManager::getInstance(
            $this->templateEngine->getId()
        );
        $this->childLayout = $postLayoutManager->createLayout(
            $layoutName,
            $this->wp_query
        );

        // This case use when the content generator is set before add child layout
        if (is_callable($this->contentGenerator)) {
            $this->childLayout->setContentGenerator(array(
                'function' => &$this->contentGenerator,
                'args' => &$this->contentGeneratorArgs,
            ));
        }
    }

    public function getChildLayout()
    {
        if (!is_a($this, PostLayoutParent::class)) {
            throw new \Exception(sprintf(
                '%s post layout is not support get child layout',
                static::get_layout_label()
            ));
        }
        return $this->childLayout;
    }
}
