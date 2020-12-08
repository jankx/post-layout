<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\PostLayout;

class LargePostWithList extends PostLayout
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
                jankx_template('common/header-text', array(
                    'text' => $args['header_text'],
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

                    $data = array(
                        'post' => $post,
                    );
                    jankx_template(array(
                        $post->post_type . '-layout/preset1/large-item',
                        'post-layout/preset1/large-item',
                        'post-layout/large-item',
                    ), $data);

                    // Create post list
                    $this->loop_start();

                    while ($this->wp_query->have_posts()) {
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
        </div>
        <?php
    }
}
