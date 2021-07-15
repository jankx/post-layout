<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\PostLayout;

class Preset1 extends PostLayout
{
    const LAYOUT_NAME = 'preset-1';

    public function get_name()
    {
        return static::LAYOUT_NAME;
    }

    public static function get_layout_label()
    {
        return sprintf(__('Preset %d', 'jankx'), 1);
    }

    protected function defaultOptions()
    {
        return array(
            'show_thumbnail' => true,
            'thumbnail_position' => 'left',
            'show_excerpt' => false,
        );
    }

    public function render($echo = true)
    {
        if (!$this->templateEngine) {
            error_log(__('The template engine is not setted to render content', 'jankx_post_layout'));
            return;
        }
        if (!$echo) {
            ob_start();
        }
        foreach ((array)$this->wp_query->query_vars['post_type'] as $post_type) {
            // This hook use to stop custom render post layout
            do_action("jankx/layout/{$post_type}/loop/init", $this->get_name(), $this);
        }
        ?>
        <div class="jankx-posts-layout left-post right-list preset-1">
            <div class="jankx-posts-layout-inner">
                <?php
                // Create first post
                $this->the_post();

                $post = $this->getCurrentPostItem();
                // Setup the post classes
                $this->createCustomPostClass($post);

                $this->templateEngine->render(array(
                    $post->post_type . '-layout/preset1/large-item',
                    'post-layout/preset1/large-item',
                    'post-layout/large-item',
                ));

                // Create post list
                $this->loop_start(true);

                while ($this->checkNextPost()) {
                    $this->the_post();
                    $this->renderLoopItem(
                        $this->getCurrentPostItem()
                    );
                }

                $this->loop_end(true);
                wp_reset_postdata();
                ?>
            </div>

            <?php if (array_get($this->options, 'show_paginate', false)) : ?>
                <?php echo jankx_paginate(); ?>
            <?php endif; ?>
        </div>
        <?php
        if (!$echo) {
            return ob_get_clean();
        }
    }
}
