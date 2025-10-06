<?php

namespace Jankx\PostLayout;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

class Utils
{
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

    /**
     * Get a value from an array using dot notation
     *
     * @param array $array The array to search in
     * @param string $key The key to search for (supports dot notation)
     * @param mixed $default The default value to return if key not found
     * @return mixed
     */
    public static function array_get($array, $key, $default = null)
    {
        if (!is_array($array)) {
            return $default;
        }

        if (is_null($key)) {
            return $array;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        if (strpos($key, '.') === false) {
            return $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * Generate HTML attributes from array
     *
     * @param array $attributes Array of attributes
     * @return string HTML attributes string
     */
    public static function generate_html_attributes($attributes)
    {
        if (empty($attributes) || !is_array($attributes)) {
            return '';
        }

        $html = '';
        foreach ($attributes as $key => $value) {
            if (is_array($value)) {
                $value = implode(' ', $value);
            }
            if (is_bool($value)) {
                if ($value) {
                    $html .= ' ' . esc_attr($key);
                }
            } else {
                $html .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
            }
        }

        return $html;
    }

    /**
     * Generate HTML attributes from array (jankx_generate_html_attributes function)
     *
     * @param array $attributes Array of attributes
     * @return string HTML attributes string
     */
    public static function jankx_generate_html_attributes($attributes)
    {
        if (empty($attributes) || !is_array($attributes)) {
            return '';
        }

        $html = '';
        foreach ($attributes as $key => $value) {
            if (is_array($value)) {
                $value = implode(' ', $value);
            }
            if (is_bool($value)) {
                if ($value) {
                    $html .= ' ' . esc_attr($key);
                }
            } else {
                $html .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
            }
        }

        return $html;
    }
}

