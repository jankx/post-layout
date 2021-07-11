<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\PostLayout;

class Preset1 extends PostLayout
{
    const NAME = 'preset-1';

    public function get_name()
    {
        return static::NAME;
    }

    protected function defaultOptions()
    {
        return array(
            'show_thumbnail' => true,
            'thumbnail_position' => 'left',
            'show_excerpt' => false,
        );
    }

    public function render()
    {
        $args = $this->options;
        ?>
        <div class="jankx-posts-layout left-post right-list preset-1">
            <?php
            if ($args['header_text']) {
                $css_classes = array('widget-text-header');
                $url = array_get($args['view_all_url'], 'url');
                if ($url) {
                    $css_classes[] = 'has-link';
                }

                jankx_template('common/header-text', array(
                    'text' => $args['header_text'],
                    'url ' => $url,
                    'class_name' => implode(' ', $css_classes),
                ));
            }
            ?>
            <div class="jankx-posts-layout-wrapper">
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
            </div>

            <?php if (array_get($args, 'show_paginate', false)) : ?>
                <?php echo jankx_paginate(); ?>
            <?php endif; ?>
        </div>
        <?php
    }
}
