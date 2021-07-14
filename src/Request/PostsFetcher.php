<?php
namespace Jankx\PostLayout\Request;

class PostsFetcher
{
    public function init()
    {
        add_action('wp_ajax_jankx-post-layout-fetch-data', array($this, 'fetch'));
        add_action('wp_ajax_nopriv_jankx-post-layout-fetch-data', array($this, 'fetch'));
    }

    public function fetch()
    {
    }
}
