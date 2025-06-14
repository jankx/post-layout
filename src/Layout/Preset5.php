<?php

namespace Jankx\PostLayout\Layout;

if (!defined('ABSPATH')) {
    exit('Cheatin huh?');
}

use Jankx\PostLayout\Layout\Carousel;

class Preset5 extends Carousel
{
    const LAYOUT_NAME = 'preset-5';

    protected $supportColumns = false;
    protected $numOfLastItems;
    protected $isCarouselItem = false;

    public function get_name()
    {
        return static::LAYOUT_NAME;
    }

    public static function get_layout_label()
    {
        return sprintf(__('Preset %d', 'jankx'), 5);
    }

    public function is_pro()
    {
        return false;
    }

    protected function get_number_of_items()
    {
        if (is_null($this->numOfLastItems)) {
            $this->numOfLastItems = apply_filters(
                'jankx/layout/post/preset5/last_items',
                array_get($this->options, 'last_columns_items', 3),
                $this
            );

            if (($this->wp_query->post_count - $this->numOfLastItems) <= 0) {
                $this->numOfLastItems = $this->wp_query->post_count - 1;
            }
        }
        return $this->numOfLastItems;
    }


    public function beforeLoop()
    {
        $this->openCarouselWrapper();

        if (array_get($this->options, 'show_nav')) {
            $this->createControls();
        }

        $this->openTrackList();
    }

    public function afterLoop($disableWTopWrapper = false)
    {
        $post_type = $this->wp_query->get('post_type');
        do_action("jankx/layout/{$post_type}/loop/end", $this->get_name(), $this);
    }

    protected function openCarouselWrapper()
    {
        parent::openCarouselWrapper();
        $this->isCarouselItem = true;
    }

    protected function closeCarouselWrap()
    {
        parent::closeCarouselWrap();
        $this->isCarouselItem = false;
    }

    public function renderLoopItem($post)
    {
        if (is_null($this->contentGenerator)) {
            if ($this->isCarouselItem) {
                return $this->templateEngine->render(
                    $this->generateSearchingLargeItemTemplates($post),
                    $this->prepareTemplateData()
                );
            }
            return $this->templateEngine->render(
                $this->generateSearchingTemplates($post),
                $this->prepareTemplateData()
            );
        }

        $args = $this->contentGeneratorArgs;
        array_push($args, $post);

        return call_user_func_array($this->contentGenerator, $args);
    }

    protected function beforeLoopItemActions($post)
    {
        $currentIndex = $this->wp_query->current_post;
        $numLastItems = $this->get_number_of_items();
        $newListIndex = $this->wp_query->post_count - $numLastItems;
        if ($currentIndex === $newListIndex) {
            $this->closeTrackList();
            $this->closeCarouselWrap();

            $newListTagWrapAttrs = array(
                'class' => array('sub-list'),
            );
            printf('<div %s>', jankx_generate_html_attributes($newListTagWrapAttrs));
        }
        if ($currentIndex < $newListIndex) {
            parent::beforeLoopItemActions($post);
        }
    }

    protected function afterLoopItemActions($post)
    {
        $currentIndex = $this->wp_query->current_post;
        $numLastItems = $this->get_number_of_items();
        $newListIndex = $this->wp_query->post_count - $numLastItems;
        $totalPost = $this->wp_query->post_count;
        if ($currentIndex < $newListIndex) {
            parent::afterLoopItemActions($post);
        }

        if ($currentIndex === $totalPost - 1) {
            echo '</div><!-- /.sub-list -->';
        }
    }

    /**
     * Generate carousel options for Preset 5
     *
     * @return array
     */
    protected function generateCarouselOptions()
    {
        $optionByCssClasses = ['carousel-wrapper', 'swiffy-slider'];

        $optionByCssClasses[] = sprintf('slider-item-show%d', 1);
        if (static::getDragEnable()) {
            $optionByCssClasses[] = 'slider-nav-mousedrag';
        }
        if ($this->getOption('autoplay', true)) {
            $optionByCssClasses[] = 'slider-nav-autoplay';
        }

        return $optionByCssClasses;
    }
}
