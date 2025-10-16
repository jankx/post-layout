<?php

namespace Jankx\PostLayout\Request;

use Exception;
use WC_Query;
use WP_Query;
use Jankx\Facades\App;
use Jankx\Foundation\Application;
use Jankx\PostLayout\Utils;


if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

class PostsFetcher
{
    const FETCH_POSTS_ACTION = 'jankx-post-layout-fetch-data';

    protected $post_type;
    protected $engine_id;

    protected $data_type;
    protected $type_name;
    protected $object_id;
    protected $thumb_pos;
    protected $data_preset;
    protected $thumb_size;
    protected $tax_query;

    protected $current_page = 1;
    protected $posts_per_page = 10;
    protected $layout = 'card';
    protected $offset;

    // Ordering parameters
    protected $order_by = 'date';
    protected $order = 'DESC';

    // Jankx Global filters supports
    protected $taxonomy = array();

    // Support Woocommerce
    protected $order_product;

    protected $originRequest = [];

    // Additional properties for frontend integration
    protected $include = array();
    protected $exclude = array();
    protected $meta_filters = array();
    protected $post_templates = array();

    // Block options for complete mapping
    protected $display_options = array();
    protected $styling = array();
    protected $layout_options = array();
    protected $pagination = array();
    protected $responsive = array();

    public function init()
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[PostsFetcher Debug] Initializing PostsFetcher with AJAX action: " . static::FETCH_POSTS_ACTION);
        }

        add_action('wp_ajax_' . static::FETCH_POSTS_ACTION, array($this, 'fetch'));
        add_action('wp_ajax_nopriv_' . static::FETCH_POSTS_ACTION, array($this, 'fetch'));

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[PostsFetcher Debug] PostsFetcher AJAX actions registered successfully");
        }
    }

    protected function parseRequestParams()
    {
        foreach ($_GET as $key => $value) {
            if ($key === "tax_query") {
                // Fix JSON when call JSON.stringify
                $value = trim($value, '\\"');

                $value = html_entity_decode(stripslashes($value));
                $value = str_replace('\\"', '"', $value);

                // Decode JSON string
                $this->tax_query = json_decode($value, true);
                continue;
            }
            if ($key === "taxonomy_filters") {
                // Handle taxonomy filters from frontend
                $value = trim($value, '\\"');
                $value = html_entity_decode(stripslashes($value));
                $value = str_replace('\\"', '"', $value);
                $taxonomyFilters = json_decode($value, true);
                if (is_array($taxonomyFilters)) {
                    $this->taxonomy = $taxonomyFilters;
                }
                continue;
            }
            if ($key === "meta_filters") {
                // Handle meta filters from frontend
                $value = trim($value, '\\"');
                $value = html_entity_decode(stripslashes($value));
                $value = str_replace('\\"', '"', $value);
                $metaFilters = json_decode($value, true);
                if (is_array($metaFilters)) {
                    $this->meta_filters = $metaFilters;
                }
                continue;
            }
            if ($key === "include") {
                // Handle include posts
                $value = trim($value, '\\"');
                $value = html_entity_decode(stripslashes($value));
                $value = str_replace('\\"', '"', $value);
                $include = json_decode($value, true);
                if (is_array($include)) {
                    $this->include = $include;
                }
                continue;
            }
            if ($key === "exclude") {
                // Handle exclude posts
                $value = trim($value, '\\"');
                $value = html_entity_decode(stripslashes($value));
                $value = str_replace('\\"', '"', $value);
                $exclude = json_decode($value, true);
                if (is_array($exclude)) {
                    $this->exclude = $exclude;
                }
                continue;
            }
            if ($key === "post_templates") {
                // Handle post templates
                $value = trim($value, '\\"');
                $value = html_entity_decode(stripslashes($value));
                $value = str_replace('\\"', '"', $value);
                $postTemplates = json_decode($value, true);
                if (is_array($postTemplates)) {
                    $this->post_templates = $postTemplates;
                }
                continue;
            }
            if ($key === "display_options") {
                // Handle display options from block
                $value = trim($value, '\\"');
                $value = html_entity_decode(stripslashes($value));
                $value = str_replace('\\"', '"', $value);
                $displayOptions = json_decode($value, true);
                if (is_array($displayOptions)) {
                    $this->display_options = $displayOptions;
                }
                continue;
            }
            if ($key === "styling") {
                // Handle styling options from block
                $value = trim($value, '\\"');
                $value = html_entity_decode(stripslashes($value));
                $value = str_replace('\\"', '"', $value);
                $styling = json_decode($value, true);
                if (is_array($styling)) {
                    $this->styling = $styling;
                }
                continue;
            }
            if ($key === "layout_options") {
                // Handle layout options from block
                $value = trim($value, '\\"');
                $value = html_entity_decode(stripslashes($value));
                $value = str_replace('\\"', '"', $value);
                $layoutOptions = json_decode($value, true);
                if (is_array($layoutOptions)) {
                    $this->layout_options = $layoutOptions;
                }
                continue;
            }
            if ($key === "pagination") {
                // Handle pagination options from block
                $value = trim($value, '\\"');
                $value = html_entity_decode(stripslashes($value));
                $value = str_replace('\\"', '"', $value);
                $pagination = json_decode($value, true);
                if (is_array($pagination)) {
                    $this->pagination = $pagination;
                }
                continue;
            }
            if ($key === "responsive") {
                // Handle responsive options from block
                $value = trim($value, '\\"');
                $value = html_entity_decode(stripslashes($value));
                $value = str_replace('\\"', '"', $value);
                $responsive = json_decode($value, true);
                if (is_array($responsive)) {
                    $this->responsive = $responsive;
                }
                continue;
            }
            if (property_exists($this, $key)) {
                $this->$key = apply_filters(
                    "jankx_post_layout_ajax_{$key}_args",
                    $value
                );
            }
        }
        $this->originRequest = $_REQUEST;
    }

    protected function checkRequestIsValid()
    {
        $isValid = $this->post_type && $this->engine_id;
        return apply_filters(
            "jankx/layout/{$this->post_type}/request/valid",
            $isValid
        );
    }

    protected function checkHasMorePost()
    {
        // Check if there are more posts available
        return false; // Default implementation - can be overridden by filters
    }

    /**
     * Build complete PostLayout options from block attributes
     * Maps all block options to PostLayout expected format
     *
     * @return array Complete options array for PostLayout
     */
    protected function buildPostLayoutOptions()
    {
        $options = [];

        // Layout options - columns and gap
        $options['columns'] = Utils::array_get($this->layout_options, 'columns', 3);
        $options['columns_tablet'] = Utils::array_get($this->layout_options, 'columnsTablet', 2);
        $options['columns_mobile'] = Utils::array_get($this->layout_options, 'columnsMobile', 1);
        $options['gap'] = Utils::array_get($this->layout_options, 'gap', 20);
        $options['gap_tablet'] = Utils::array_get($this->layout_options, 'gapTablet', 15);
        $options['gap_mobile'] = Utils::array_get($this->layout_options, 'gapMobile', 10);

        // Display options - what to show/hide
        $options['show_title'] = Utils::array_get($this->display_options, 'showTitle', true);
        $options['show_excerpt'] = Utils::array_get($this->display_options, 'showExcerpt', true);
        $options['show_meta'] = Utils::array_get($this->display_options, 'showMeta', true);
        $options['show_thumbnail'] = Utils::array_get($this->display_options, 'showThumbnail', true);
        $options['show_read_more'] = Utils::array_get($this->display_options, 'showReadMore', true);
        $options['excerpt_length'] = Utils::array_get($this->display_options, 'excerptLength', 20);
        $options['meta_fields'] = Utils::array_get($this->display_options, 'metaFields', ['date', 'author', 'categories']);

        // Thumbnail options (from display_options or fallback to old properties)
        $options['thumbnail_position'] = Utils::array_get($this->display_options, 'thumbnailPosition', $this->thumb_pos ?: 'top');
        $options['thumbnail_size'] = Utils::array_get($this->display_options, 'thumbnailSize', $this->thumb_size ?: 'medium');

        // Styling options - visual effects
        $options['hover_effect'] = Utils::array_get($this->styling, 'hoverEffect', 'lift');
        $options['border_radius'] = Utils::array_get($this->styling, 'borderRadius', 8);
        $options['shadow'] = Utils::array_get($this->styling, 'shadow', 'medium');

        // Animation options
        if (isset($this->styling['enableAnimations'])) {
            $options['enable_animations'] = $this->styling['enableAnimations'];
            $options['animation_duration'] = Utils::array_get($this->styling, 'animationDuration', 300);
        }

        // Performance options
        if (isset($this->styling['lazyLoading'])) {
            $options['lazy_loading'] = $this->styling['lazyLoading'];
        }
        if (isset($this->styling['cssContainment'])) {
            $options['css_containment'] = $this->styling['cssContainment'];
        }

        // Responsive styling overrides
        if (!empty($this->responsive['enabled'])) {
            if (isset($this->styling['borderRadiusTablet'])) {
                $options['border_radius_tablet'] = $this->styling['borderRadiusTablet'];
            }
            if (isset($this->styling['borderRadiusMobile'])) {
                $options['border_radius_mobile'] = $this->styling['borderRadiusMobile'];
            }
            if (isset($this->styling['shadowTablet'])) {
                $options['shadow_tablet'] = $this->styling['shadowTablet'];
            }
            if (isset($this->styling['shadowMobile'])) {
                $options['shadow_mobile'] = $this->styling['shadowMobile'];
            }
        }

        // Pagination options
        if (!empty($this->pagination['enabled'])) {
            $options['show_paginate'] = true;
            $options['pagination_type'] = Utils::array_get($this->pagination, 'type', 'numbers');
            $options['max_numbers'] = Utils::array_get($this->pagination, 'maxNumbers', 10);
            $options['show_first_last'] = Utils::array_get($this->pagination, 'showFirstLast', false);
            $options['show_ellipsis'] = Utils::array_get($this->pagination, 'showEllipsis', true);
            $options['show_current_page'] = Utils::array_get($this->pagination, 'showCurrentPage', true);
            $options['ellipsis_position'] = Utils::array_get($this->pagination, 'ellipsisPosition', 'both');
            $options['prev_text'] = Utils::array_get($this->pagination, 'prevText', __('Previous', 'jankx'));
            $options['next_text'] = Utils::array_get($this->pagination, 'nextText', __('Next', 'jankx'));
            $options['show_icons'] = Utils::array_get($this->pagination, 'showIcons', true);
            $options['show_page_info'] = Utils::array_get($this->pagination, 'showPageInfo', false);

            // Load more specific options
            $paginationType = Utils::array_get($this->pagination, 'type', 'numbers');
            if ($paginationType === 'load_more' || $paginationType === 'infinite_scroll') {
                $options['load_more_text'] = Utils::array_get($this->pagination, 'loadMoreText', __('Load More', 'jankx'));
                $options['loading_text'] = Utils::array_get($this->pagination, 'loadingText', __('Loading...', 'jankx'));
                $options['no_more_text'] = Utils::array_get($this->pagination, 'noMoreText', __('No More Posts', 'jankx'));
                $options['posts_per_load'] = Utils::array_get($this->pagination, 'postsPerLoad', 6);
                $options['show_spinner'] = Utils::array_get($this->pagination, 'showSpinner', true);
                $options['hide_when_complete'] = Utils::array_get($this->pagination, 'hideWhenComplete', true);

                if ($paginationType === 'infinite_scroll') {
                    $options['trigger_distance'] = Utils::array_get($this->pagination, 'triggerDistance', 100);
                    $options['show_loading_indicator'] = Utils::array_get($this->pagination, 'showLoadingIndicator', true);
                    $options['show_back_to_top'] = Utils::array_get($this->pagination, 'showBackToTop', false);
                }
            }

            // AJAX options
            $options['ajax'] = Utils::array_get($this->pagination, 'ajax', false);
            $options['update_url'] = Utils::array_get($this->pagination, 'updateURL', true);
            $options['scroll_to_top'] = Utils::array_get($this->pagination, 'scrollToTop', false);
            $options['show_loading_state'] = Utils::array_get($this->pagination, 'showLoadingState', true);

            // Accessibility options
            if (isset($this->pagination['keyboardNav'])) {
                $options['keyboard_nav'] = $this->pagination['keyboardNav'];
            }
            if (isset($this->pagination['touchSupport'])) {
                $options['touch_support'] = $this->pagination['touchSupport'];
            }
        } else {
            $options['show_paginate'] = false;
        }

        return $options;
    }

    public function createQueryDataTypeArgs(&$args)
    {
        switch ($this->data_type) {
            case 'taxonomy':
                $args['tax_query'][] = array(
                    'taxonomy' => $this->type_name,
                    'field' => 'term_id',
                    'terms' => intval($this->object_id),
                );
                return $args;
        }
    }

    protected function createWooCommerceProductOrders(&$args, $orderBy, $direction)
    {
        if (!class_exists(WC_Query::class)) {
            return $args;
        }
        $wc_query = new WC_Query();
        $orderArgs = $wc_query->get_catalog_ordering_args($orderBy, $direction);

        return array_merge($args, $orderArgs);
    }

    protected function createWpQueryFromRequest($args)
    {
        $postType = Utils::array_get($args, 'post_type', 'post');
        do_action("jankx/posts/fetcher/{$postType}/query/start", $args, $this);
        $wp_query = apply_filters("jankx/posts/fetcher/{$postType}/query", null, $args, $this);
        if (is_null($wp_query)) {
            $wp_query = new WP_Query($args);
        }

        do_action("jankx/posts/fetcher/{$postType}/query/end", $args, $this);

        return $wp_query;
    }

    public function createWordPressQuery()
    {
        $args = array(
            'post_type' => $this->post_type,
            'posts_per_page' => $this->posts_per_page,
            'post_status' => 'publish',
        );

        if ($this->current_page > 1) {
            $args['paged'] = $this->current_page;
        }

        // Add ordering
        if ($this->order_by) {
            $args['orderby'] = $this->order_by;
        }
        if ($this->order) {
            $args['order'] = $this->order;
        }

        if ($this->data_type && $this->type_name && $this->object_id) {
            $this->createQueryDataTypeArgs($args);
        }

        if (!empty($this->taxonomy)) {
            foreach ($this->taxonomy as $taxonomy => $terms) {
                $args['tax_query'][] = array(
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'terms' => $terms,
                    'operator' => 'IN'
                );
            }
        }

        if ($this->order_product) {
            $sort_order = explode('-', $this->order_product);
            $this->createWooCommerceProductOrders(
                $args,
                Utils::array_get($sort_order, 0),
                Utils::array_get($sort_order, 1, 'asc')
            );
        }

        if (!empty($this->tax_query)) {
            if (isset($args['tax_query'])) {
                $args['tax_query'] = array_merge($args['tax_query'], $this->tax_query);
            } else {
                $args['tax_query'] = $this->tax_query;
            }
        }

        if ($this->offset > 0) {
            $args['offset'] = intval($this->offset);
        }

        // Handle include posts
        if (!empty($this->include)) {
            $args['post__in'] = array_map('intval', $this->include);
        }

        // Handle exclude posts
        if (!empty($this->exclude)) {
            $args['post__not_in'] = array_map('intval', $this->exclude);
        }

        // Handle meta filters
        if (!empty($this->meta_filters)) {
            foreach ($this->meta_filters as $meta_key => $meta_value) {
                $args['meta_query'][] = array(
                    'key' => $meta_key,
                    'value' => $meta_value,
                    'compare' => 'LIKE'
                );
            }
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[PostsFetcher Debug] Query args before filter: " . print_r($args, true));
        }

        $args = apply_filters(
            "jankx/layout/{$this->post_type}/args",
            $args,
            $this->originRequest,
            $this->data_preset,
            $this
        );

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[PostsFetcher Debug] Query args after filter: " . print_r($args, true));
        }

        $wp_query = $this->createWpQueryFromRequest($args);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[PostsFetcher Debug] Post IDs: " . implode(', ', wp_list_pluck($wp_query->posts, 'ID')));
        }

        return $wp_query;
    }



    public function fetch()
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[PostsFetcher Debug] Fetch method called");
            error_log("[PostsFetcher Debug] GET parameters: " . print_r($_GET, true));
            error_log("[PostsFetcher Debug] REQUEST parameters: " . print_r($_REQUEST, true));
        }

        try {

        $this->parseRequestParams();

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[PostsFetcher Debug] Engine ID from request: " . $this->engine_id);
            error_log("[PostsFetcher Debug] Post type: " . $this->post_type);
            if (!empty($this->post_templates)) {
                error_log("[PostsFetcher Debug] Post templates: " . print_r($this->post_templates, true));
            }
        }

        if (!$this->checkRequestIsValid()) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("[PostsFetcher Debug] Request validation failed");
            }
            wp_send_json_error(__('Please check your request parameters', 'jankx'));
        }


        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[PostsFetcher Debug] Request validation passed");
            error_log("[PostsFetcher Debug] Attempting to resolve template engine");
        }

        $jankxApp = Application::getInstance();
        if (!$jankxApp) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("[PostsFetcher Debug] Jankx application not available");
            }
            wp_send_json_error(__('Jankx application not available', 'jankx'));
        }

        // Resolve engine based on engine_id parameter
        $engineAlias = 'template.engine.' . $this->engine_id;

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[PostsFetcher Debug] Jankx application found, resolving: " . $engineAlias);
        }

        try {
            $templateEngine = App::make($engineAlias);

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("[PostsFetcher Debug] Template engine resolved: " . get_class($templateEngine));
                error_log("[PostsFetcher Debug] Engine ID: " . $this->engine_id);
            }
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("[PostsFetcher Debug] Error resolving template engine: " . $e->getMessage());
            }
            wp_send_json_error(__('Template engine not available: ' . $e->getMessage(), 'jankx'));
        }

        /**
         * @var \Jankx\PostLayout\PostLayoutManager
         */
        $postLayoutManager = App::make('postlayout.manager');
        $wp_query = $this->createWordPressQuery();

        $loopItemLayoutType = apply_filters("jankx/posts/fetcher/{$this->post_type}/content_layout", 'default');
        $loopItemLayout     = $postLayoutManager->getLoopItemContentByType($loopItemLayoutType);

        $postLayout = $postLayoutManager->createLayout(
            $this->layout,
            $wp_query,
            $loopItemLayout
        );

        // Build complete options from all block attributes
        $options = $this->buildPostLayoutOptions();
        $postLayout->setOptions($options);

        // Only disable loop start/end if NOT a block preview request
        // Check both header and query parameter
        $isBlockPreview = (
            (isset($_SERVER['HTTP_X_JANKX_BLOCK_PREVIEW']) && $_SERVER['HTTP_X_JANKX_BLOCK_PREVIEW'] === '1') ||
            (isset($_GET['block_preview']) && $_GET['block_preview'] === '1') ||
            (isset($_REQUEST['block_preview']) && $_REQUEST['block_preview'] === '1')
        );

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[PostsFetcher Debug] About to render post layout");
            error_log("[PostsFetcher Debug] Post layout class: " . get_class($postLayout));
            error_log("[PostsFetcher Debug] Is block preview: " . ($isBlockPreview ? 'YES' : 'NO'));
            error_log("[PostsFetcher Debug] block_preview param: " . (isset($_REQUEST['block_preview']) ? $_REQUEST['block_preview'] : 'NOT SET'));
        }

        if (!$isBlockPreview) {
            $postLayout->disableLoopStartLoopEnd();
        }

        $content = $postLayout->render(false);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[PostsFetcher Debug] Template rendered, content length: " . ($content ? strlen($content) : 0));
            if ($content && strlen($content) < 100) {
                error_log("[PostsFetcher Debug] Content preview: " . substr($content, 0, 200));
            }
        }

        // Convert posts to array format expected by frontend
        $posts = array();
        if ($wp_query->have_posts()) {
            while ($wp_query->have_posts()) {
                $wp_query->the_post();
                $post = get_post();

                $posts[] = array(
                    'ID' => $post->ID,
                    'title' => get_the_title($post->ID),
                    'permalink' => get_permalink($post->ID),
                    'excerpt' => get_the_excerpt($post->ID),
                    'thumbnail' => get_the_post_thumbnail_url($post->ID, 'medium'),
                    'date' => get_the_date('', $post->ID),
                    'post_type' => $post->post_type,
                    'meta' => array(
                        'date' => get_the_date('', $post->ID),
                        'author' => get_the_author_meta('display_name', $post->post_author)
                    )
                );
            }
            wp_reset_postdata();
        }

        $response = array(
            'content' => $content, // Keep for backward compatibility
            'posts' => $posts,     // New format for frontend
            'more_posts' => $this->checkHasMorePost(),
            'query_info' => array(
                'total_posts' => $wp_query->found_posts,
                'found_posts' => $wp_query->post_count,
                'max_pages' => $wp_query->max_num_pages,
                'current_page' => $wp_query->get('paged') ?: 1,
                'posts_per_page' => $wp_query->get('posts_per_page'),
                'post_type' => $this->post_type,
                'layout' => $this->layout,
                'engine_id' => $this->engine_id
            ),
            'post_templates' => $this->post_templates
        );

        if ($this->offset > 0) {
            $response['next_offset'] = $wp_query->get('posts_per_page') + $wp_query->get('offset');
        }
        wp_send_json_success($response);

        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("[PostsFetcher Debug] Exception caught: " . $e->getMessage());
                error_log("[PostsFetcher Debug] Exception trace: " . $e->getTraceAsString());
            }
            wp_send_json_error(__('An error occurred while fetching posts: ' . $e->getMessage(), 'jankx'));
        } catch (Error $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("[PostsFetcher Debug] Fatal error caught: " . $e->getMessage());
                error_log("[PostsFetcher Debug] Error trace: " . $e->getTraceAsString());
            }
            wp_send_json_error(__('A fatal error occurred: ' . $e->getMessage(), 'jankx'));
        }
    }
}
