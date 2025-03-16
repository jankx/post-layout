<?php

namespace Jankx\PostLayout\Request;

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

    public function init()
    {
        add_action('wp_ajax_' . static::FETCH_POSTS_ACTION, array($this, 'fetch'));
        add_action('wp_ajax_nopriv_' . static::FETCH_POSTS_ACTION, array($this, 'fetch'));
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
            if (property_exists($this, $key)) {
                $this->$key = apply_filters(
                    'jankx_post_layout_ajax_{$key}_args',
                    $value
                );
            }
        }
    }

    protected function checkRequestIsValid()
    {
        $isValid = $this->post_type && $this->engine_id;
        return apply_filters(
            'jankx/layout/{$this->post_type}/request/valid',
            $isValid
        );
    }

    protected function checkHasMorePost()
    {
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
        do_action('jankx/posts/fetcher/query/start', $args, $this);

        $wp_query = apply_filters('jankx/posts/fetcher/query', null, $args, $this);
        if (is_null($wp_query)) {
            $wp_query = new WP_Query($args);
        }

        do_action('jankx/posts/fetcher/query/end', $args, $this);

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

        if ($this->data_type & $this->type_name & $this->object_id) {
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


        $args = apply_filters(
            "jankx/layout/{$this->post_type}/args",
            $args,
            $this->data_preset,
            $this
        );

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
        return $this->createWpQueryFromRequest($args);
    }



    public function fetch()
    {
        $this->parseRequestParams();
        if (!$this->checkRequestIsValid()) {
            wp_send_json_error(__('Please check your request parameters', 'jankx'));
        }
        $templateEngine = Template::getEngine($this->engine_id);
        $postLayoutManager = PostLayoutManager::getInstance($templateEngine->getId());
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

        $response = array(
            'content' => $postLayout->render(false),
            'more_posts' => $this->checkHasMorePost(),
        );

        if ($this->offset > 0) {
            $response['next_offset'] = $wp_query->get('posts_per_page') + $wp_query->get('offset');
        }

        wp_send_json_success($response);
    }
}
