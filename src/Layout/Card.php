<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\PostLayout;

class Card extends PostLayout
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
                <?php
                // Create post list
                jankx_post_loop_start('card', $args);

                while ($this->wp_query->have_posts()) {
                    $this->wp_query->the_post();
                    $post = $this->wp_query->post;
                    $data = array(
                        'post' => $post,
                        'show_thumbnail' => array_get($args, 'show_thumbnail', true),
                        'thumbnail_size' => array_get($args, 'thumbnail_size', 'thumbnail'),
                    );
                    jankx_template(array(
                        $post->post_type . '-layout/card/loop-item',
                        'post-layout/card/loop-item',
                    ), $data);
                }

                jankx_post_loop_end('card', $args);
                wp_reset_postdata();
                ?>
            </div>
        </div>
        <?php
    }
}
