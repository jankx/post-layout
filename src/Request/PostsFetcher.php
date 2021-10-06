<?php
namespace Jankx\PostLayout\Request;

use WP_Query;
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

    protected $current_page = 1;
    protected $posts_per_page = 10;
    protected $layout = 'card';

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

        return new WP_Query($args);
    }

    public function fetch()
    {
        $this->parseRequestParams();
        if (!$this->checkRequestIsValid()) {
            wp_send_json_error(__('Please check your request parameters', 'jankx_ecommerce'));
        }
        $templateEngine = Template::getEngine($this->engine_id);
        $postLayoutManager = PostLayoutManager::getInstance($templateEngine->getId());
        $postLayout = $postLayoutManager->createLayout(
            $this->layout,
            $this->createWordPressQuery()
        );
        $postLayout->setOptions([
            'thumbnail_position' => $this->thumb_pos ? $this->thumb_pos : 'top',
        ]);

        $postLayout->disableLoopStartLoopEnd();

        wp_send_json_success(array(
            'content' => $postLayout->render(false),
            'more_posts' => $this->checkHasMorePost(),
        ));
    }
}
