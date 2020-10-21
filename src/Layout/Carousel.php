<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\PostLayout;

class Carousel extends PostLayout
{
    protected $currentIndex = 0;

    public function get_name() {
        return 'carousel';
    }

    protected function open()
    {
    }
    protected function close()
    {
    }

    protected function defaultOptions() {
        return array(
            'large_first_post' => false,
            'show_thumbnail' => true,
            'thumbnail_position' => 'left',
            'header_text' => '',
            'columns' => 4,
            'rows' => 1
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
                $this->loop_start('carousel', $args);

                $this->createSplide();

                    $this->createControls();

                    $this->createTrackList();
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
                            $this->createSlideItem();
                            jankx_template(array(
                                $post->post_type . '-layout/carousel/loop-item',
                                'post-layout/carousel/loop-item',
                                'post-layout/loop-item',
                            ), $data);
                            $this->closeSlideItem();
                        }
                    $this->closeTrackList();
                $this->closeSplide();

                $this->loop_end('carousel', $args);
                wp_reset_postdata();
                ?>
            </div>
        </div>
        <?php
        $this->createJsMountSlide();
    }

    protected function createSplide() {
        $splideClasses = array('splide');
        $attributes = array(
            'class' => $splideClasses,
            'id' => sprintf('jankx-post-layout-%d', $this->getId())
        );
        echo '<div ' . jankx_generate_html_attributes($attributes) . '>';
    }

    protected function createControls() {
        jankx_template('post-layout/carousel/nav');
    }

    protected function createTrackList() {
    ?>
    <div class="splide__track">
        <ul class="splide__list">
    <?php
    }

    protected function createSlideItem() {
        if ($this->currentIndex === 0) {
            echo '<li class="splide__slide">';
        }
    }

    protected function closeSlideItem() {
        $this->currentIndex += 1;

        // Reset the current index
        if ($this->options['rows'] == $this->currentIndex) {
            $this->currentIndex = 0;
        }

        if ($this->currentIndex === 0 || !$this->wp_query->have_posts()) {
            echo '</li>';
        }
    }

    protected function closeTrackList() {
    ?>
        </ul>
    </div><!-- Close .splide__track -->
    <?php
    }

    protected function closeSplide() {
        echo '</div> <!-- Close .splide -->';
    }

    public function createJsMountSlide() {
        execute_script(jankx_template('post-layout/carousel/script', array(
            'id' => sprintf('jankx-post-layout-%d', $this->getId()),
            'var' => sprintf('jankx_post_layout_%d', $this->getId()),
        ), null, false));
    }
}
