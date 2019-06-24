<?php
namespace Jankx\PostLayouts;

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

    public function __construct() {
        if (\Jankx::isRequest('frontend')) {
            add_action('jankx_setup_environment', array($this, 'setupEnvironment'));
        }
        $this->registerPostLayoutsShortcodes();
    }

    public function setupEnvironment($jankx) {
        $jankx->post = function() {
            return new Data();
        };
    }

    public function registerPostLayoutsShortcodes()
    {
    }
}
