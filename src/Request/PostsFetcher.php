<?php

namespace Jankx\PostLayout\Request;

use Exception;
use Jankx\Facades\App;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

use WP_Query;
use WC_Query;
use Jankx\PostLayout\PostLayoutManager;
use Jankx\Template\Template;

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

    // Jankx Global filters supports
    protected $taxonomy = array();

    // Support Woocommerce
    protected $order_product;

    protected $originRequest = [];

    // Additional properties for frontend integration
    protected $include = array();
    protected $exclude = array();
    protected $meta_filters = array();

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
        $postType = array_get($args, 'post_type', 'post');
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
        );

        if ($this->current_page > 1) {
            $args['paged'] = $this->current_page;
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
                array_get($sort_order, 0),
                array_get($sort_order, 1, 'asc')
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

        $args = apply_filters(
            "jankx/layout/{$this->post_type}/args",
            $args,
            $this->originRequest,
            $this->data_preset,
            $this
        );
        return $this->createWpQueryFromRequest($args);
    }



    public function fetch()
    {
        try {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("[PostsFetcher Debug] Fetch method called");
                error_log("[PostsFetcher Debug] GET parameters: " . print_r($_GET, true));
            }

            $this->parseRequestParams();
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("[PostsFetcher Debug] Error parsing request params: " . $e->getMessage());
            }
            wp_send_json_error(__('Error parsing request parameters: ' . $e->getMessage(), 'jankx'));
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[PostsFetcher Debug] Engine ID from request: " . $this->engine_id);
            error_log("[PostsFetcher Debug] Post type: " . $this->post_type);
        }

        if (!$this->checkRequestIsValid()) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("[PostsFetcher Debug] Request validation failed");
            }
            wp_send_json_error(__('Please check your request parameters', 'jankx'));
        }

        try {

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[PostsFetcher Debug] Request validation passed");
            error_log("[PostsFetcher Debug] Attempting to resolve template engine");
        }

        $jankxApp = App::getInstance();
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

        // Create or get PostLayoutManager instance
        $postLayoutManager = PostLayoutManager::getInstance($this->engine_id);
        if (!$postLayoutManager) {
            $postLayoutManager = PostLayoutManager::createInstance($templateEngine);
        }
        $wp_query = $this->createWordPressQuery();

        $loopItemLayoutType = apply_filters("jankx/posts/fetcher/{$this->post_type}/content_layout", null);
        $loopItemLayout     = $postLayoutManager->getLoopItemContentByType($loopItemLayoutType);


        $postLayout = $postLayoutManager->createLayout(
            $this->layout,
            $wp_query,
            $loopItemLayout
        );
        $postLayout->setOptions([
            'thumbnail_position' => $this->thumb_pos ? $this->thumb_pos : 'top',
            'thumbnail_size' => $this->thumb_size ? $this->thumb_size : 'medium',
        ]);

        $postLayout->disableLoopStartLoopEnd();

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[PostsFetcher Debug] About to render post layout");
            error_log("[PostsFetcher Debug] Layout: " . $this->layout);
            error_log("[PostsFetcher Debug] Post type: " . $this->post_type);
        }

        $content = $postLayout->render(false);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[PostsFetcher Debug] Template rendered, content length: " . ($content ? strlen($content) : 0));
            if ($content && strlen($content) < 100) {
                error_log("[PostsFetcher Debug] Content preview: " . substr($content, 0, 200));
            }
        }

        $response = array(
            'content' => $content,
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
            )
        );

        if ($this->offset > 0) {
            $response['next_offset'] = $wp_query->get('posts_per_page') + $wp_query->get('offset');
        }

        wp_send_json_success($response);

        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("[PostsFetcher Debug] Fatal error in fetch method: " . $e->getMessage());
                error_log("[PostsFetcher Debug] Stack trace: " . $e->getTraceAsString());
            }
            wp_send_json_error(__('An error occurred while fetching posts: ' . $e->getMessage(), 'jankx'));
        }
    }

}
