<?php
namespace Jankx\PostLayout;

use WP_Query;
use Jankx\Template\Template;
use Jankx\PostLayout\Constracts\PostLayout as PostLayoutConstract;

use function wp_parse_args;

abstract class PostLayout implements PostLayoutConstract
{
    protected static $layoutInstances = array();
    protected static $isElementor = false;

    protected $wp_query;
    protected $options;
    protected $supportColumns = false;

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

    abstract public function get_name();

    protected function defaultOptions() {
        return array();
    }

    public function setOptions($options)
    {
        // Parse post layout with default options
        $this->options = wp_parse_args(
            $options,
            $this->defaultOptions()
        );
    }

    public function getOptions() {
        return (array)$this->options;
    }

    public function loop_start() {
        $postsListClasses = array('jankx-posts', sprintf('%s-layout', $this->get_name()));
        if ($this->supportColumns && !empty($this->options['columns'])) {
            $postsListClasses[] = 'columns-' . $this->options['columns'];
        }
        $attributes = array(
            'class' => $postsListClasses,
        );

        echo '<div ' . jankx_generate_html_attributes($attributes) . '>';

        // This hook use to start custom render post layout
        do_action('jankx_post_loop_start', $this->get_name(), $this);
    }

    public function loop_end() {
        // This hook use to stop custom render post layout
        do_action('jankx_post_loop_end', $this->get_name(), $this);

        // Close posts list wrapper
        echo '</div><!-- End .jankx-posts -->';
    }

    protected function createCustomPostClass(&$post = null) {
    }

    public static function postClasses($classes) {
        global $post;
        if (isset($post->custom_post_class)) {
            $classes[] = is_array($post->custom_post_class)
                ? implode(' ', $post->custom_post_class)
                : $post->custom_post_class;
        }
        return $classes;
    }
}
