<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\PostLayout;

class ListLayout extends PostLayout
{
    protected function open()
    {
    }
    protected function close()
    {
    }

    public function render()
    {
        $args = wp_parse_args(
            $this->options,
            array(
                'wp_query' => $this->wp_query,
                'large_first_post' => false,
                'show_thumbnail' => true,
                'thumbnail_position' => 'left',
                'header_text' => '',
            )
        );
        ?>
        <div class="jankx-posts-layout list">
            <?php
            if ($args['header_text']) {
                jankx_template('common/header-text', array(
                    'text' => $args['header_text'],
                    'url ' => $args['view_all_url'],
                ));
            }
            ?>
            <div class="posts-layout-wrapper">
                    <div class="jankx-layout-inner">
                        <?php
                        if ($args['large_first_post']) {
                            // Create first post
                            $this->wp_query->the_post();
                            $data = array(
                                'post' => $this->wp_query->post,
                                'thumbnail_position' => $args['thumbnail_position'],
                            );
                            jankx_template('post/list/large-post', $data);
                        }

                        // Create post list
                        jankx_post_loop_start('list');

                        while ($this->wp_query->have_posts()) {
                            $this->wp_query->the_post();
                            $post = $this->wp_query->post;
                            $data = array(
                                'post' => $post,
                                'show_thumbnail' => $args['show_thumbnail'],
                                'post_class' => $this->getPostClass($post, $args),
                            );
                            jankx_template('post/list/loop-post', $data);
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
