<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\PostLayout;

class LargePostWithList extends PostLayout
{
    public function render()
    {
        $args = array_merge(
            $this->options,
            array(
                'wp_query' => $this->wp_query
            )
        );
        ?>
        <div class="jankx-posts left-post right-list">
            <?php
                jankx_template('common/header-text', array(
                    'text' => $args['header_text'],
                ));
            ?>
            <div class="posts-list-wrapper">
                    <div class="jankx-inner">
                        <?php
                        // Create first post
                        $this->wp_query->the_post();
                        $data = array(
                            'post' => $this->wp_query->post,
                        );
                        jankx_template('post/loop/large-image', $data);


                        // Create post list
                        jankx_post_loop_start();

                        while ($this->wp_query->have_posts()) {
                            $this->wp_query->the_post();
                            jankx_template('post/loop/list-item', $data);
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
