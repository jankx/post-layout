<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\PostLayout;

class Grid extends PostLayout
{
    public function get_name() {
        return 'grid';
    }

    protected function defaultOptions() {
        return array(
            'large_first_post' => false,
            'show_thumbnail' => true,
            'thumbnail_position' => 'left',
            'header_text' => '',
        );
    }

    public function render()
    {
        $args = $this->options;
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
                $this->loop_start('card', $args);

                while ($this->wp_query->have_posts()) {
                    $this->wp_query->the_post();
                    $post = &$this->wp_query->post;
                    // Setup the post classes
                    $this->createCustomPostClass($post);

                    $data = array(
                        'post' => $post,
                        'show_title' => array_get($args, 'show_title', true),
                        'show_thumbnail' => array_get($args, 'show_thumbnail', true),
                        'thumbnail_size' => array_get($args, 'thumbnail_size', 'thumbnail'),
                    );
                    jankx_template(array(
                        $post->post_type . '-layout/card/loop-item',
                        'post-layout/card/loop-item',
                        'post-layout/loop-item',
                    ), $data);
                }

                $this->loop_end();
                wp_reset_postdata();
            ?>
            </div>
        </div>
        <?php
    }
}
