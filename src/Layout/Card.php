<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\PostLayout;

class Card extends PostLayout
{
    protected $supportColumns = true;

    public function get_name()
    {
        return 'card';
    }


    protected function defaultOptions()
    {
        return array(
            'show_thumbnail' => true,
            'thumbnail_position' => 'top',
            'header_text' => '',
            'show_excerpt' => false,
        );
    }

    public function render()
    {
        $args = $this->options;
        ?>
        <div class="jankx-posts-layout card">
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

                while ($this->checkNextPost()) {
                    $this->wp_query->the_post();
                    $post = &$this->wp_query->post;

                    // Setup the post classes
                    $this->createCustomPostClass($post);
                    jankx_template(array(
                        $post->post_type . '-layout/card/loop-item',
                        'post-layout/card/loop-item',
                        'post-layout/loop-item',
                    ), $this->prepareTemplateData());
                }

                $this->loop_end('card', $args);
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
