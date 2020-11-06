<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\PostLayout;

class ListLayout extends PostLayout
{
    public function get_name()
    {
        return 'list';
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

                    while ($this->wp_query->have_posts()) {
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
            </div>
        </div>
        <?php
    }
}
