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
    protected $current_page;
    protected $posts_per_page;
    protected $layout;

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

    public function createWordPressQuery()
    {
        $args = array(
            'post_type' => $this->post_type,
        );

        return new WP_Query($args);
    }

    public function fetch()
    {
        $this->parseRequestParams();
        if (!$this->checkRequestIsValid()) {
            wp_send_json_success();
        }
        $templateEngine = Template::getEngine($this->engine_id);
        $postLayoutManager = PostLayoutManager::getInstance($templateEngine->getId());
        $postLayout = $postLayoutManager->createLayout(
            $this->layout,
            $this->createWordPressQuery()
        );

        $postLayout->disableLoopStartLoopEnd();

        $postLayout->render();
        exit();
    }
}
