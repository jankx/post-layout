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
                        $data = array(
                            'post' => $this->wp_query->post,
                        );
                        jankx_template('post/preset1/large-post', $data);


                        // Create post list
                        jankx_post_loop_start('left-thumbnail');

                        while ($this->wp_query->have_posts()) {
                            $this->wp_query->the_post();

                            $post_type = $this->wp_query->post->post_type;

                            jankx_template($post_type . '/preset1/loop-post', $data);
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
