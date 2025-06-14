<?php

namespace Jankx\PostLayout;

if (!defined('ABSPATH')) {
    exit('Cheatin huh?');
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
}
