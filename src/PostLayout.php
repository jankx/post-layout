<?php
namespace Jankx\PostLayout;

use WP_Query;
use Jankx\PostLayout\Constracts\PostLayout as PostLayoutConstract;

abstract class PostLayout implements PostLayoutConstract
{
    protected $wp_query;

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
    }
}
