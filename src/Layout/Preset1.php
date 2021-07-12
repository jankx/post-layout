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
        $args = $this->options;
        ?>
        <div class="jankx-posts-layout left-post right-list preset-1">
            <div class="jankx-posts-layout-inner">
                <?php
                // Create first post
                $this->wp_query->the_post();
                $post = &$this->wp_query->post;
                // Setup the post classes
                $this->createCustomPostClass($post);

                jankx_template(array(
                    $post->post_type . '-layout/preset1/large-item',
                    'post-layout/preset1/large-item',
                    'post-layout/large-item',
                ));

                // Create post list
                $this->loop_start();

                while ($this->checkNextPost()) {
                    $this->wp_query->the_post();

                    $post = $this->wp_query->post;
                    jankx_template(array(
                        $post->post_type . 'layout-/preset1/loop-item',
                        'post-layout/preset1/loop-item',
                        'post-layout/loop-item',
                    ), $this->prepareTemplateData());
                }

                $this->loop_end();
                wp_reset_postdata();
                ?>
            </div>

            <?php if (array_get($args, 'show_paginate', false)) : ?>
                <?php echo jankx_paginate(); ?>
            <?php endif; ?>
        </div>
        <?php
    }
}
