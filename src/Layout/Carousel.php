<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\Constracts\PostLayoutChildren;
use Jankx\PostLayout\PostLayout;

class Carousel extends PostLayout implements PostLayoutChildren
{
    const LAYOUT_NAME = 'carousel';

    protected $currentIndex = 0;
    protected $disableCarouselItem = false;
    protected $addCustomPostClassClosure;

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
            $this->createCarouselWrapper();
                $this->createControls();
                $this->createTrackList();
        }
    }

    public function afterLoop($disableWTopWrapper = false)
    {
        if (!$this->isContentOnly) {
                $this->closeTrackList();
                $this->createIndicators();
            $this->closeCarouselWrap();
        }
        parent::afterLoop($disableWTopWrapper);
    }

    protected function createCarouselWrapper()
    {
        $carouselClasses = array('carousel-wrapper', sprintf('columns-%d', $this->options['columns']));
        $attributes = array(
            'class' => $carouselClasses,
            'id' => sprintf('%s--Carousel', $this->getInstanceId()),
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
        <div class="swiffy-slider slider-item-show3 slider-nav-round slider-nav-page" id="swiffy-animation">
            <ul class="slider-container <?php echo $post_type; ?>s">
        <?php
    }

    protected function beforeLoopItemActions($post)
    {
        parent::beforeLoopItemActions($post);
        if ($this->disableCarouselItem) {
            return;
        }

        $rows = array_get($this->options, 'rows', 1);
        if ($rows > 1) {
            if ($this->currentIndex % intval($rows) === 0) {
                echo '<li class="slide-visible">';
            }
        } else {
            echo '<li>';
        }
    }

    protected function createIndicators() {
        ?>
        <ul class="slider-indicators">
            <li class="active"></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
        </ul>
        <?php
    }

    protected function afterLoopItemActions($post)
    {
        if ($this->disableCarouselItem) {
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
        </div><!-- Close .swiffy-slider -->
        <?php
    }

    protected function closeCarouselWrap()
    {
        echo '</div> <!-- Close .carousel-wrap -->';
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
        if ($this->addCustomPostClassClosure) {
            remove_action('post_class', $this->addCustomPostClassClosure);
            unset($this->addCustomPostClassClosure);
        }

        $args = apply_filters(
            'jankx/post/layout/carousel/args',
            $this->generateCarouselOptions(),
            $this->getInstanceId(),
            $this->options
        );

        execute_script($this->templateEngine->render('post-layout/carousel/script', array(
            'id' => sprintf('%s--Carousel', $this->getInstanceId()),
            'var' => preg_replace('/[\s|\-]/', '_', $this->getInstanceId()),
            'config' => $args,
        ), null, false));
    }

    public function addCustomClassToPostItem($customClass)
    {
        $this->disableCarouselItem = true;
        $this->addCustomPostClassClosure = function ($classes) use($customClass) {
            $classes = array_merge($classes,
                is_array($customClass) ? $customClass : [$customClass]
            );
            return array_unique($classes);
        };
        add_action('post_class', $this->addCustomPostClassClosure);
    }
}
