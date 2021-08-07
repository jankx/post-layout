<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\Constracts\PostLayoutChildren;
use Jankx\PostLayout\PostLayout;

class Carousel extends PostLayout implements PostLayoutChildren
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

    public function is_pro()
    {
        return true;
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
        );
    }

    public function beforeLoop()
    {
        parent::beforeLoop();
            $this->createSplide();
                $this->createControls();
                $this->createTrackList();
    }

    public function afterLoop($disableWTopWrapper = false)
    {
                $this->closeTrackList();
            $this->closeSplide();
        parent::afterLoop($disableWTopWrapper);
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
        $this->templateEngine->render('post-layout/carousel/nav');
    }

    protected function createTrackList()
    {
        ?>
        <div class="splide__track">
            <ul class="splide__list">
        <?php
    }

    protected function beforeLoopItemActions($post)
    {
        parent::beforeLoopItemActions($post);
        if ($this->currentIndex === 0) {
            echo '<li class="splide__slide">';
        }
    }

    protected function afterLoopItemActions($post)
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
        parent::afterLoopItemActions($post);
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

    public function afterRenderLayout()
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

        execute_script($this->templateEngine->render('post-layout/carousel/script', array(
            'id' => sprintf('jankx-post-layout-%d', $this->getId()),
            'var' => sprintf('jankx_post_layout_%d', $this->getId()),
            'config' => $args,
        ), null, false));
    }
}
