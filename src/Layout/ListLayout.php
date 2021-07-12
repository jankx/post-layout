<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\PostLayout;

class ListLayout extends PostLayout
{
    const LAYOUT_NAME = 'list';

    public function get_name()
    {
        return 'list';
    }

    public static function get_layout_label()
    {
        return __('List', 'jankx');
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

    protected function createCustomPostClass(&$post = null)
    {
        if (is_a($post, \WP_Post::class)) {
            $classes = array();
            if ($this->options['show_thumbnail']) {
                $classes[] = 'thumbnail-' . $this->options['thumbnail_position'];
            }
            $post->custom_post_class = $classes;
        }
    }

    public function render($echo = true)
    {
        $args = $this->options;
        ?>
        <div class="jankx-posts-layout list">
            <div class="jankx-layout-inner">
                <?php
                if ($args['large_first_post']) {
                    // Create first post
                    $this->wp_query->the_post();
                    // Setup the post classes
                    $this->createCustomPostClass($this->wp_query->post);
                    jankx_template(array(
                        $post->post_type . '-layout/list-large-item',
                        'post-layout/list/large-item',
                        'post-layout/large-item',
                    ), $this->prepareTemplateData());
                }

                // Create post list
                $this->loop_start('list', $args);

                while ($this->checkNextPost()) {
                    $this->wp_query->the_post();
                    $post = &$this->wp_query->post;
                    // Setup the post classes
                    $this->createCustomPostClass($post);

                    $itemClasses = array();
                    if ($args['show_thumbnail']) {
                        $itemClasses = array('thumbnail-' . $args['thumbnail_position']);
                    }
                    jankx_template(array(
                        $post->post_type . '-layout/list/loop-item',
                        'post-layout/list/loop-item',
                        'post-layout/loop-item',
                    ), $this->prepareTemplateData());
                }

                $this->loop_end('list', $args);
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
