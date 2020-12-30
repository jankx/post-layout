<?php
namespace Jankx\PostLayout;

use Jankx\PostLayout\Constracts\PostLayout;
use Jankx\PostLayout\PostLayoutManager;

class PostTemplateLoader
{
    public function load()
    {
        add_action('jankx_template_page_index_content', array($this, 'render'));
        add_action('jankx_template_page_archive_content', array($this, 'render'));
        add_action('jankx_template_before_post_content', array($this, 'renderPostMetas'));
    }

    public function render($page = 'home')
    {
        $layoutManager = PostLayoutManager::getInstance();
        $layoutStyle   = apply_filters(
            "jankx_post_layout_page_{$page}_style",
            PostLayoutManager::LIST_LAYOUT
        );
        $layoutCls     = $layoutManager->getLayoutClass($layoutStyle);

        // Create post layout style instance
        $postLayoutInstance = new $layoutCls($GLOBALS['wp_query']);

        // Render posts
        if (is_a($postLayoutInstance, PostLayout::class)) {
            echo $postLayoutInstance->render();
        }
    }

    public function renderPostMetas()
    {
        jankx_template('common/post-metas');
    }
}
