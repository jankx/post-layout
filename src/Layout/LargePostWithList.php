<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\PostLayout;

class LargePostWithList extends PostLayout
{
    const NAME = 'preset-1';

    public function render()
    {
        $args = array_merge(
            $this->options,
            array(
                'wp_query' => $this->wp_query,
                'show_thumbnail' => true,
                'thumbnail_position' => 'left',
            )
        );
        ?>
        <div class="jankx-posts-layout left-post right-list">
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
                        $post = $this->wp_query->post;
                        $data = array(
                            'post' => $post,
                            'post_class' => $this->getPostClass($post, $args)
                        );
                        jankx_template(array(
                            $post->post_type . '-layout/preset1/large-item',
                            'post-layout/preset1/large-item'
                        ), $data);

                        // Create post list
                        jankx_post_loop_start('left-thumbnail');

                        while ($this->wp_query->have_posts()) {
                            $this->wp_query->the_post();

                            $post_type = $this->wp_query->post->post_type;

                            jankx_template(array(
                                $post_type . 'layout-/preset1/loop-item',
                                'post-layout/preset1/loop-item'
                            ), $data);
                        }

                        jankx_post_loop_end();
                        wp_reset_postdata();
                        ?>
                    </div>
            </div>
        </div>
        <?php
    }
}
