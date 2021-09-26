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
            'show_dot' => false,
            'show_nav' => true,
        );
    }

    public function beforeLoop()
    {
        parent::beforeLoop();
        if (!$this->isContentOnly) {
            $this->createSplide();
                $this->createControls();
                $this->createTrackList();
        }
    }

    public function afterLoop($disableWTopWrapper = false)
    {
        if (!$this->isContentOnly) {
                $this->closeTrackList();
            $this->closeSplide();
        }
        parent::afterLoop($disableWTopWrapper);
    }

    protected function createSplide()
    {
        $splideClasses = array('splide', 'carousel-wrapper', sprintf('columns-%d', $this->options['columns']));
        $attributes = array(
            'class' => $splideClasses,
            'id' => sprintf('jankx-post-layout-%d', $this->getId())
        );
        echo '<div ' . jankx_generate_html_attributes($attributes) . '>';
    }

    protected function createControls()
    {
        if (array_get($this->options, 'show_nav')) {
            $this->templateEngine->render('post-layout/carousel/nav');
        }
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
        if ($this->disableSplideItem) {
            return;
        }

        $rows = array_get($this->options, 'rows', 1);
        if ($rows > 1) {
            if ($this->currentIndex % intval($rows) === 0) {
                echo '<li class="splide__slide">';
            }
        } else {
            echo '<li class="splide__slide">';
        }
    }

    protected function afterLoopItemActions($post)
    {
        if ($this->disableSplideItem) {
            return parent::afterLoopItemActions($post);
        }

        $rows = intval(array_get($this->options, 'rows', 1));
        if ($rows > 1) {
            if ($this->currentIndex % $rows === ($rows - 1)) {
                echo '</li>';
            } elseif ($this->wp_query->current_post === ($this->wp_query->post_count - 1)) {
                echo '</li>';
            }
            $this->currentIndex += 1;
        } else {
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
        $columns = array_get($this->options, 'columns', 4);
        $mobile_columns = array_get($this->options, 'columns_mobile', 1);
        $tablet_columns = array_get($this->options, 'columns_tablet', 2);

        return array(
            'perPage' => $columns,
            'pagination' => array_get($this->options, 'show_dot', false),
            'arrows' => array_get($this->options, 'show_nav', false),
            'breakpoints' => array(
                '800' => array(
                    'perPage' => $tablet_columns ? $tablet_columns : 2,
                ),
                '600' => array(
                    'perPage' => $mobile_columns ? $mobile_columns : 1
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
