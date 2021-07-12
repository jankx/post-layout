<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\PostLayout;

class Carousel extends PostLayout
{
    const LAYOUT_NAME = 'carousel';

    protected $currentIndex = 0;

    public function get_name()
    {
        return static::LAYOUT_NAME;
    }

    public static function get_layout_label()
    {
        return __('Carousel', 'jankx');
    }

    protected function defaultOptions()
    {
        return array(
            'show_thumbnail' => true,
            'thumbnail_position' => 'top',
            'header_text' => '',
            'columns' => 4,
            'rows' => 1,
            'show_excerpt' => false,
            'item_style' => 'card',
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

    public function render($echo = true)
    {
        $args = $this->options;
        ?>
        <div class="jankx-posts-layout carousel">
            <?php
            // Create post list
            $this->loop_start('carousel', $args);

            $this->createSplide();

                $this->createControls();
                $this->createTrackList();
            while ($this->checkNextPost()) {
                $this->wp_query->the_post();
                $post = &$this->wp_query->post;
                // Setup the post classes
                $this->createCustomPostClass($post);
                $this->createSlideItem();
                jankx_template(array(
                    $post->post_type . '-layout/carousel/loop-item',
                    'post-layout/carousel/loop-item',
                    'post-layout/loop-item',
                ), $this->prepareTemplateData());
                $this->closeSlideItem();
            }
                $this->closeTrackList();
            $this->closeSplide();

            $this->loop_end('carousel', $args);
            wp_reset_postdata();

            $this->createSlidesOptionsVariable();
            ?>
        </div>
        <?php
    }

    protected function createSplide()
    {
        $splideClasses = array('splide');
        $attributes = array(
            'class' => $splideClasses,
            'id' => sprintf('jankx-post-layout-%d', $this->getId())
        );
        echo '<div ' . jankx_generate_html_attributes($attributes) . '>';
    }

    protected function createControls()
    {
        jankx_template('post-layout/carousel/nav');
    }

    protected function createTrackList()
    {
        ?>
    <div class="splide__track">
        <ul class="splide__list">
        <?php
    }

    protected function createSlideItem()
    {
        if ($this->currentIndex === 0) {
            echo '<li class="splide__slide">';
        }
    }

    protected function closeSlideItem()
    {
        $this->currentIndex += 1;

        if ($this->currentIndex == $this->options['rows']) {
            $this->currentIndex = 0;
        }

        $currentPostIndex = $this->wp_query->current_post;
        $totalIndex = $this->wp_query->post_count - 1;
        if ($this->currentIndex === 0 || $currentPostIndex >= $totalIndex) {
            echo '</li>';
        }
    }

    protected function closeTrackList()
    {
        ?>
        </ul>
    </div><!-- Close .splide__track -->
        <?php
    }

    protected function closeSplide()
    {
        echo '</div> <!-- Close .splide -->';
    }

    public function createSlidesOptionsVariable()
    {
        $args = array(
            'perPage' => array_get($this->options, 'columns', 4),
            'breakpoints' => array(
                '800' => array(
                    'perPage' => 2,
                ),
                '600' => array(
                    'perPage' => 1
                )
            )
        );

        execute_script(jankx_template('post-layout/carousel/script', array(
            'id' => sprintf('jankx-post-layout-%d', $this->getId()),
            'var' => sprintf('jankx_post_layout_%d', $this->getId()),
            'config' => $args,
        ), null, false));
    }
}
