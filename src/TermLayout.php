<?php
namespace Jankx\PostLayout;

use WP_Term_Query;
use Jankx\PostLayout\Constracts\TermLayout;

abstract class TermLayout implement TermLayout
{
    protected $wp_term_query;

    public function __construct($wp_term_query = null)
    {
        if (!is_null($wp_term_query)) {
            $this->set_term_query($wp_term_query);
        }
    }

    public function set_wp_term_query($wp_term_query)
    {
        if (is_a($wp_term_query, WP_Term_Query::class)) {
            $this->wp_term_query = $wp_term_query;
        }
    }

    public function render($echo = false)
    {
    }
}
