<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\Constracts\PostLayoutChildren;
use Jankx\PostLayout\PostLayout;

class Carousel extends PostLayout implements PostLayoutChildren
{
    const LAYOUT_NAME = 'carousel';

    protected $currentIndex = 0;
    protected $disableSplideItem = false;
    protected $splideToPostClasses;

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
        $post_type = $this->wp_query->get('post_type');
        ?>
        <div class="splide__track">
            <ul class="splide__list <?php echo $post_type; ?>s">
        <?php
    }

    protected function beforeLoopItemActions($post)
    {
        parent::beforeLoopItemActions($post);
        $currentIndex = $this->wp_query->current_post;
        $rows = array_get($this->options, 'rows', 1);
        if (!$this->disableSplideItem && ($currentIndex % $rows == 0)) {
            echo '<li class="splide__slide">';
        }
    }

    protected function afterLoopItemActions($post)
    {
        $rows         = array_get($this->options, 'rows', 1);
        $currentIndex = $this->wp_query->current_post;
        $totalPost    = $this->wp_query->post_count;

        $isEndRowIndex = ($currentIndex / $rows) === ($rows - 1);
        $isEndLoop     = $currentIndex === ($totalPost - 1);

        if (!$this->disableSplideItem && ($isEndRowIndex || $isEndLoop)) {
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

    protected function generateCarouselOptions()
    {
        return array(
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
    }

    public function afterRenderLayout()
    {
        if ($this->splideToPostClasses) {
            remove_action('post_class', $this->splideToPostClasses);
            unset($this->splideToPostClasses);
        }

        $args = $this->generateCarouselOptions();

        execute_script($this->templateEngine->render('post-layout/carousel/script', array(
            'id' => sprintf('jankx-post-layout-%d', $this->getId()),
            'var' => sprintf('jankx_post_layout_%d', $this->getId()),
            'config' => $args,
        ), null, false));
    }

    public function setItemAsSplide()
    {
        $this->disableSplideItem = true;
        $this->splideToPostClasses = function ($classes) {
            array_unshift($classes, 'splide__slide');

            return $classes;
        };
        add_action('post_class', $this->splideToPostClasses);
    }
}
