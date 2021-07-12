<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\PostLayout;

class Grid extends PostLayout
{
    const LAYOUT_NAME = 'grid';

    public function get_name()
    {
        return static::LAYOUT_NAME;
    }

    public static function get_layout_label()
    {
        return __('Grid', 'jankx');
    }

    protected function defaultOptions()
    {
        return array(
            'large_first_post' => false,
            'show_thumbnail' => true,
            'thumbnail_position' => 'left',
            'header_text' => '',
            'show_excerpt' => false,
        );
    }

    public function render($echo = true)
    {
        $args = $this->options;
        ?>
        <div class="jankx-posts-layout grid">
            <?php
                // Create post list
                $this->loop_start('card', $args);

            while ($this->wp_query->have_posts()) {
                $this->wp_query->the_post();
                $post = &$this->wp_query->post;
                // Setup the post classes
                $this->createCustomPostClass($post);
                jankx_template(array(
                    $post->post_type . '-layout/card/loop-item',
                    'post-layout/card/loop-item',
                    'post-layout/loop-item',
                ), $this->prepareTemplateData());
            }

                $this->loop_end();
                wp_reset_postdata();
            ?>

            <?php if (array_get($args, 'show_paginate', false)) : ?>
                <?php echo jankx_paginate(); ?>
            <?php endif; ?>
        </div>
        <?php
    }
}
