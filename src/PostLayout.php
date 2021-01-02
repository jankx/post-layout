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
    protected static $customDataFields = array();

    protected $instanceId;
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
        if (empty(static::$layoutInstances)) {
            $this->instanceId = 1;
        } else {
            $this->instanceId = max(static::$layoutInstances) + 1;
        }
        array_push(static::$layoutInstances, $this->instanceId);
    }

    public function getId()
    {
        return $this->instanceId;
    }

    abstract public function get_name();

    protected function defaultOptions()
    {
        return array();
    }

    public function setOptions($options)
    {
        // Parse post layout with default options
        $this->options =  apply_filters(
            'jankx_post_layout_set_options',
            wp_parse_args(
                $options,
                $this->defaultOptions()
            ),
            $this
        );
    }

    public function getOptions()
    {
        return (array)$this->options;
    }

    public function loop_start()
    {
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

    public function loop_end()
    {
        // This hook use to stop custom render post layout
        do_action('jankx_post_loop_end', $this->get_name(), $this);

        // Close posts list wrapper
        echo '</div><!-- End .jankx-posts -->';
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

    protected function prepareTemplateData()
    {
        $templateData = array(
            'post' => $this->wp_query->post,
            'show_title' => array_get($this->options, 'show_title', true),
            'show_excerpt' => array_get($this->options, 'show_excerpt', false),
            'show_thumbnail' => array_get($this->options, 'show_thumbnail', true),
            'thumbnail_size' => array_get($this->options, 'thumbnail_size', 'thumbnail'),
            'post_meta_features' => array_get($this->options, 'post_meta_features', array()),
            '_post_layout' => __CLASS__,
        );
        foreach (static::$customDataFields as $field => $defaultValue) {
            if (!isset($this->options[$field])) {
                $this->options[$field] = $defaultValue;
            }
            $templateData[$field] = $this->options[$field];
        }

        return $templateData;
    }

    public static function addCustomDataField($fieldName, $defaultValue = null)
    {
        static::$customDataFields[$fieldName] = $defaultValue;
    }
}
