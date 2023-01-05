<?php
namespace Jankx\PostLayout;

use WP_Query;
use WC_Product;
use Jankx\TemplateEngine\Engine;
use Jankx\PostLayout\Constracts\PostLayoutParent;
use Jankx\PostLayout\Constracts\PostLayout as PostLayoutConstract;
use Jankx\PostLayout\Exceptions\PropertyNotFoundException;
use Jankx\PostLayout\PostLayoutManager;

use function wp_parse_args;

abstract class PostLayout implements PostLayoutConstract
{
    const MODE_APPEND = 'append';
    const MODE_REPLACE = 'replace';

    protected static $layoutInstances = array();
    protected static $isElementor = false;
    protected static $instanceIndex = 1;

    protected $id;
    protected $instanceId;

    protected $wp_query;
    protected $templateEngine;
    protected $childLayout;
    protected $hasChildren;
    protected $isContentOnly;

    protected $options = array(
        'item_style' => 'defaut',
        'excerpt_length' => false,
    );
    protected $supportColumns = false;
    protected $contentGenerator;
    protected $contentGeneratorArgs = array();
    protected $contentWrapperTag = false;
    protected $mode = 'replace';

    protected $dataProcessors = array();

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
        $this->id = self::$instanceIndex;
        $this->instanceId = sprintf('post-%s-layout-%s', $this->get_name(), self::$instanceIndex);

        if (is_a($this->wp_query, WP_Query::class) && $this->wp_query->is_main_query()) {
            $this->instanceId = 'jankx-main-layout';
        }
        self::$instanceIndex++;

        array_push(static::$layoutInstances, $this->instanceId);

        $this->options     = $this->defaultOptions();
        $this->hasChildren = is_a($this, PostLayoutParent::class);
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        throw new PropertyNotFoundException(
            sprintf('Property "%s" is not found', $name),
            40001
        );
    }

    public function setTemplateEngine($engine)
    {
        if (is_a($engine, Engine::class)) {
            $this->templateEngine = $engine;
        }
    }

    public function setInstanceId($id)
    {
        $this->instanceId = $id;
    }

    public function getInstanceId()
    {
        return $this->instanceId;
    }

    public function getId()
    {
        return $this->id;
    }


    protected function defaultOptions()
    {
        return array(
            'max_loop_items' => -1,
        );
    }


    public function addOption($optionName, $optionValue)
    {
        $this->options[$optionName] = $optionValue;
    }

    public function setOptions($options)
    {
        // Parse post layout with default options
        $options = apply_filters("jankx_post_layout_{$this::get_name()}_set_options", $options);
        foreach ($options as $optionName => $optionValue) {
            $this->addOption($optionName, $optionValue);
        }

        // Use this case when set option after create child layout
        if ($this->childLayout) {
            $this->childLayout->setOptions($this->options);
        }
    }

    public function getOptions($key = null)
    {
        if (is_null($key)) {
            return (array)$this->options;
        }
        return isset($this->options[$key]) ? $this->options[$key] : null;
    }

    public function is_pro()
    {
        return false;
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

    protected function createDataTaxonomiesAttributes()
    {
        $ret = array();
        $tax_query = $this->wp_query->tax_query;
        if (!empty($tax_query->queries)) {
            $ret = $tax_query->queries;
        }
        return $ret;
    }

    public function postLayoutStart($disableWTopWrapper = false)
    {
        $post_type = $this->wp_query->get('post_type');
        if (is_array($post_type)) {
            $post_type = array_shift($post_type);
        }
        if ($this->isContentOnly) {
            if (!$this->hasChildren) {
                // This hook use to start custom render post layout
                do_action("jankx/layout/{$post_type}/loop/start", $this->get_name(), $this);
            }
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
            $tabletColumns = array_get($this->options, 'columns_mobile');
            if ($tabletColumns > 0) {
                $postsListClasses[] = "tablet-columns-{$tabletColumns}";
            }
            $mobileColumns = array_get($this->options, 'columns_mobile');
            if ($mobileColumns > 0) {
                $postsListClasses[] = "mobile-columns-{$mobileColumns}";
            }
        }

        $attributes = array(
            'class' => $postsListClasses,
            'data-mode' => array_get($this->options, 'pagination_type') == 'load_more' ? static::MODE_APPEND : $this->mode,
        );

        $paginationType = array_get($this->options, 'pagination_type');
        if (array_get($this->options, 'show_paginate') &&  in_array($paginationType, array('load_more'))) {
            $attributes['data-tax_query'] = json_encode($this->createDataTaxonomiesAttributes());
        }

        if ($this->contentWrapperTag) {
            $attributes['data-content-wrapper'] = $this->contentWrapperTag;
        }

        echo '<div ' . jankx_generate_html_attributes($attributes) . '>';

        if (!$this->hasChildren) {
            do_action("jankx/layout/{$post_type}/loop/start", $this->get_name(), $this);
        }
    }

    public function postLayoutEnd($disableWTopWrapper = false)
    {
        if (!$this->hasChildren) {
            foreach ((array)$this->wp_query->get('post_type') as $post_type) {
                // This hook use to stop custom render post layout
                do_action("jankx/layout/{$post_type}/loop/end", $this->get_name(), $this);
            }
        }
        if ($this->isContentOnly) {
            return;
        }
            // Close posts list wrapper
        echo '</div><!-- End .jankx-posts -->';
        if (!$disableWTopWrapper) {
            echo '</div><!-- End .jankx-post-layout-wrap -->';
        }
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

    protected function prepareTemplateData($data = array())
    {
        $postClasses = array('loop-item');
        if (($itemStyle = array_get($this->options, 'item_style', 'default')) != 'default') {
            array_push($postClasses, sprintf('style-%s', $itemStyle));
        }

        $templateData = wp_parse_args(
            $data,
            array(
                'attributes'         => jankx_generate_html_attributes(array()),
                'data_index'         => $this->wp_query->current_post,
                'show_title'         => array_get($this->options, 'show_title', true),
                'show_excerpt'       => array_get($this->options, 'show_excerpt', false),
                'show_thumbnail'     => array_get($this->options, 'show_thumbnail', true),
                'thumbnail_size'     => array_get($this->options, 'thumbnail_size', 'thumbnail'),
                'post_meta_features' => array_get($this->options, 'post_meta_features', array()),
                'post_title_tag'     => 'h3',
                'post_classes'       => apply_filters(
                    'jankx/layout/post/item/classes',
                    $postClasses,
                    $this->getCurrentPostItem(),
                    $this->options
                )
            )
        );

        if (!empty($this->dataProcessors)) {
            foreach ($this->dataProcessors as $dataProcessor) {
                $data = call_user_func_array(
                    $dataProcessor,
                    array(
                        $templateData,
                        $this->wp_query->post,
                        $this->options,
                        $this
                    )
                );

                if (is_array($data)) {
                    $templateData = $data;
                }
            }
        }

        if (($data_preset = array_get($this->options, 'data_preset'))) {
            $templateData['data_preset'] = $data_preset;
        }

        return apply_filters(
            'jankx_post_layout_parse_item_data',
            $templateData,
            $this->wp_query->post,
            $this
        );
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
        if (is_a($this->wp_query->post, WC_Product::class)) {
            $product = clone($this->wp_query->post);
            $this->wp_query->post = get_post($this->wp_query->post->get_id());

            $GLOBALS['post'] = $this->wp_query->post;
            $GLOBALS['product'] = $product;
        }
    }

    public function getCurrentPostItem()
    {
        return $this->wp_query->post;
    }

    protected function generateSearchingLargeItemTemplates($post)
    {
        if (($item_style = array_get($this->options, 'item_style', 'default')) !== 'default') {
            return array(
                "post-layout/{$this->get_name()}/$post->post_type-{$item_style}-large-item",
                "post-layout/{$this->get_name()}/{$item_style}-large-item",
                "post-layout/$post->post_type-{$item_style}-large-item",
                "post-layout/{$item_style}-large-item",
                'post-layout/large-item',
            );
        }

        return array(
            "post-layout/{$this->get_name()}/$post->post_type-large-item",
            "post-layout/{$this->get_name()}/large-item",
            'post-layout/large-item'
        );
    }

    protected function generateSearchingTemplates(&$post)
    {
        if (($item_style = array_get($this->options, 'item_style', 'default')) !== 'default') {
            return array(
                "post-layout/{$this->get_name()}/$post->post_type-{$item_style}-item",
                "post-layout/{$this->get_name()}/{$item_style}-loop-item",
                "post-layout/$post->post_type-{$item_style}-item",
                "post-layout/{$item_style}-loop-item",
                'post-layout/loop-item',
            );
        }

        return array(
            "post-layout/{$this->get_name()}/$post->post_type-item",
            "post-layout/{$this->get_name()}/loop-item",
            "post-layout/{$post->post_type}-item",
            'post-layout/loop-item'
        );
    }

    public function renderLoopItem($post)
    {
        if (is_null($this->contentGenerator)) {
            return $this->templateEngine->render(
                $this->generateSearchingTemplates($post),
                $this->prepareTemplateData()
            );
        }

        $args = $this->contentGeneratorArgs;

        array_push($args, $post);
        array_push($args, $this->wp_query);

        return call_user_func_array($this->contentGenerator, $args);
    }

    protected function createWrapAttributes()
    {
        $attributes = array(
            'class' => array('jankx-post-layout-wrap'),
            'id' => $this->getInstanceId(),
        );

        if (!$this->hasChildren) {
            $attributes['data-post-type'] = $this->wp_query->get('post_type');
            $attributes['data-posts-per-page'] = $this->wp_query->get('posts_per_page');
            $attributes['data-layout'] = $this->get_name();
            $attributes['data-engine-id'] = $this->templateEngine->getId();
            $attributes['data-thumbnail-position'] = array_get($this->options, 'thumbnail_position', 'top');
            $attributes['data-thumbnail-size'] = array_get($this->options, 'thumbnail_size');
        }

        if (($data_preset = array_get($this->options, 'data_preset'))) {
            $attributes['data-preset'] = $data_preset;
        }

        return $attributes;
    }

    protected function beforeLoopItemActions($post)
    {
        do_action('jankx/layout/post/loop/item/before', $post, $this->wp_query, $this);
    }

    protected function afterLoopItemActions($post)
    {
        do_action('jankx/layout/post/loop/item/after', $post, $this->wp_query, $this);
    }

    protected function renderDefaultPagination($paginationType)
    {
        switch ($paginationType) {
            case 'load_more';
                $this->templateEngine->render(
                    'common/paginate/load-more',
                    array(
                        'items' => array_get(
                            $this->options,
                            'load_more_items',
                            apply_filters('jankx/commom/load_more/items', 6)
                        ),
                        'wrap_id' => $this->getInstanceId(),
                    )
                );
                break;
            default:
                echo jankx_paginate($this->wp_query);
                break;
        }
    }

    protected function afterRenderLayout()
    {
        if (array_get($this->options, 'show_paginate', false)) {
            $paginationType = array_get($this->options, 'pagination_type');
            $customPagination = "paginate_{$paginationType}_actions";
            if (has_action($customPagination)) {
                return do_action($customPagination);
            }
            $this->renderDefaultPagination($paginationType);
        }
    }

    protected function beforeLoop()
    {
        $post_type = $this->wp_query->get('post_type');
        if (is_array($post_type)) {
            $post_type = array_shift($post_type);
        }
        do_action("jankx/layout/{$post_type}/loop/before", $this->get_name(), $this);
    }

    protected function afterLoop()
    {
        $post_type = $this->wp_query->get('post_type');
        if (is_array($post_type)) {
            $post_type = array_shift($post_type);
        }
        do_action("jankx/layout/{$post_type}/loop/end", $this->get_name(), $this);
    }

    public function excerptLenght($length)
    {
        if (array_get($this->options, 'excerpt_length')) {
            return $this->options['excerpt_length'];
        }
        return $length;
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

        $post_type = $this->wp_query->get('post_type');
        if (is_array($post_type)) {
            foreach ($post_type as $t) {
                do_action("jankx/layout/{$t}/loop/init", $this->get_name(), $this);
            }
        } else {
            do_action("jankx/layout/{$post_type}/loop/init", $this->get_name(), $this);
        }
        add_filter('excerpt_length', array($this, 'excerptLenght'));
        ?>
            <?php
            // Create post list
            $this->postLayoutStart();

            $this->beforeLoop();
            while ($this->checkNextPost()) {
                $this->the_post();
                $post = $this->getCurrentPostItem();

                $this->beforeLoopItemActions($post);
                $this->renderLoopItem($post);
                $this->afterLoopItemActions($post);
            }
            $this->afterLoop();

            $this->postLayoutEnd();
            $this->afterRenderLayout();

            wp_reset_postdata();
            remove_filter('excerpt_length', array($this, 'excerptLenght'));
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
        $this->childLayout->setOptions($this->options);

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

    public function getOption($optionName, $defaultValue = null)
    {
        if (isset($this->options[$optionName])) {
            return $this->options[$optionName];
        }
        return $defaultValue;
    }

    public function hasContent()
    {
        if (is_a($this->wp_query, WP_Query::class)) {
            return $this->wp_query->have_posts();
        }
        return false;
    }

    public function addDataProcessor($callback)
    {
        if (is_callable($callback)) {
            array_push($this->dataProcessors, $callback);
        }
    }
}
