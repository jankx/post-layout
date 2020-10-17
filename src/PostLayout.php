<?php
namespace Jankx\PostLayout;

use WP_Query;
use Jankx\Template\Template;
use Jankx\PostLayout\Constracts\PostLayout as PostLayoutConstract;

use function wp_parse_args;

abstract class PostLayout implements PostLayoutConstract
{
    protected $wp_query;
    protected $options;

    protected static $layoutInstances = array();
    protected static $isElementor = false;

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
    }

    public function setOptions($options)
    {
        $this->options = wp_parse_args($options, array(
        ));
    }

    public function getPostClass($post, $args) {
        $classes = array();

        if ($args['show_thumbnail']) {
            $classes[] = 'thumbnail-' . $args['thumbnail_position'];
        }

        // Hotfix for Elementor not include post type in post_class when editing
        if (static::$isElementor || (defined('DOING_AJAX') && $_REQUEST['action'] === 'elementor_ajax')) {
            $classes[] = $post->post_type;
        }

        return apply_filters('jankx_post_layout_post_class', $classes, $post);
    }
}
