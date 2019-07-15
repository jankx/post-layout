<?php
namespace Jankx\PostLayout;

use Jankx;
use Jankx\PostLayout\Data\Post as PostData;
use Jankx\PostLayout\Shortcodes\PostLayout;
use Jankx\PostLayout\UI\RelatedPost;

class Manager
{
    protected static $instance;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        if (Jankx::isRequest('frontend')) {
            add_action('jankx_setup_environment', array($this, 'setupEnvironment'));
        }
        add_action('init', array($this, 'registerPostLayoutShortcodes'));
    }

    public function setupEnvironment($jankx)
    {
        $jankx->post = function ($postID = null) {
            return new PostData($postID);
        };
    }

    public function registerPostLayoutShortcodes()
    {
        $postLayout = new PostLayout();
        add_shortcode('jankx_post_layout', array(new PostLayout(), 'render'));
    }
}
