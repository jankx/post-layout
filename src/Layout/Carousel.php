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
            'wrap_tag' => 'div',
            'show_excerpt' => false,
            'show_dot' => false,
            'show_nav' => true,
            'autoplay_interval' => 3000
        );
    }

    public function beforeLoop()
    {
        parent::beforeLoop();
        if (!$this->isContentOnly) {
            $this->openCarouselWrapper();
                $this->openTrackList();
        }
    }

    public function afterLoop($disableWTopWrapper = false)
    {
        if (!$this->isContentOnly) {
                $this->closeTrackList();
                $this->createControls();
                $this->createIndicators();
            $this->closeCarouselWrap();
        }
        parent::afterLoop($disableWTopWrapper);
    }

    protected function openCarouselWrapper()
    {


        $carouselWrapperAttributes = [
            'class' => $this->generateCarouselOptions(),
            'data-slider-nav-autoplay-interval'=> $this->getOption('autoplay_interval', 3000),
        ];

        echo sprintf('<div %s>', jankx_generate_html_attributes($carouselWrapperAttributes));
    }

    protected function closeCarouselWrap()
    {
        echo '</div>';
    }

    protected function generateCarouselOptions()
    {
        $optionByCssClasses = ['carousel-wrapper', 'swiffy-slider', 'slider-nav-touch'];

        $optionByCssClasses[] = sprintf('slider-item-show%d', $this->getOption('columns', 4));
        if (static::getDragEnable()) {
            $optionByCssClasses[] = 'slider-nav-mousedrag';
        }
        if ($this->getOption('autoplay', false)) {
            $optionByCssClasses[] = 'slider-nav-autoplay';
        }

        return $optionByCssClasses;
    }

    protected function createControls()
    {
        if (array_get($this->options, 'show_nav')) {
            $this->templateEngine->render('post-layout/carousel/nav');
        }
    }

    protected function openTrackList()
    {
        $classes = [sprintf('%s-list', $this->wp_query->get('post_type')), 'slider-container'];

        echo sprintf('<%s %s>', $this->getOption('wrap_tag_name', 'div'), jankx_generate_html_attributes([
            'class' =>  $classes
        ]));
    }


    protected function closeTrackList()
    {
        echo '</' . $this->getOption('wrap_tag', 'div') . '>';
    }

    protected function beforeLoopItemActions($post)
    {
    }

    protected function createIndicators()
    {
        if (array_get($this->options, 'show_dot')) {
            $this->templateEngine->render(
                'post-layout/carousel/indicators',
                [
                    'layout' => $this,
                    'total_items' => $this->wp_query->found_posts,
                    'wp_query' => $this->wp_query,
                ]
            );
        }
    }

    protected function afterLoopItemActions($post)
    {
        if ($this->disableCarouselItem) {
            return parent::afterLoopItemActions($post);
        }


        parent::afterLoopItemActions($post);
    }


    public function afterRenderLayout()
    {
        if ($this->addCustomPostClassClosure) {
            remove_action('post_class', $this->addCustomPostClassClosure);
            unset($this->addCustomPostClassClosure);
        }
    }

    public function addCustomClassToPostItem($customClass)
    {
        $this->disableCarouselItem = true;
        $this->addCustomPostClassClosure = function ($classes) use ($customClass) {
            $classes = array_merge(
                $classes,
                is_array($customClass) ? $customClass : [$customClass]
            );
            return array_unique($classes);
        };
        add_action('post_class', $this->addCustomPostClassClosure);
    }

    public static function getDragEnable()
    {
        return apply_filters('jankx/post/layout/slider/drag/enable', false);
    }
}
