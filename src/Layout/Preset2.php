<?php

namespace Jankx\PostLayout\Layout;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

use Jankx\PostLayout\PostLayout;
use Jankx\PostLayout\Contracts\PostLayoutChildren;

class Preset2 extends PostLayout implements PostLayoutChildren
{
    const LAYOUT_NAME = 'preset-2';

    protected $supportColumns = false;
    protected $currentPresetColumnIndex = 1;

    public function get_name()
    {
        return static::LAYOUT_NAME;
    }

    public static function get_layout_label()
    {
        return sprintf(__('Preset %d', 'jankx'), 2);
    }

    protected function defaultOptions()
    {
        return array(
            'show_thumbnail' => true,
            'thumbnail_position' => 'left',
            'show_excerpt' => false,
        );
    }

    public function is_pro()
    {
        return false;
    }

    public function getBreakpoint()
    {
        $posts_per_page = $this->wp_query->get('posts_per_page');
        return $posts_per_page > 10 ? ceil($posts_per_page / 2) : 5;
    }

    protected function beforeLoopItemActions($post)
    {
        $break_point = $this->getBreakpoint();
        $current_index = $this->wp_query->current_post;

        if (in_array($current_index, array(0, $break_point))) {
            $attributes = array(
                'class' => array(
                    'preset-column',
                    sprintf('group-%d', $this->currentPresetColumnIndex),
                    sprintf('columns-%s', array_get($this->options, 'columns', 1))
                )
            );

            if ($this->wp_query->post_count <= 5) {
                $attributes['class'][] = 'full-width';
            }
            echo sprintf('<div %s>', jankx_generate_html_attributes($attributes));

            $this->currentPresetColumnIndex += 1;
        }

        parent::beforeLoopItemActions($post);
    }

    protected function afterLoopItemActions($post)
    {
        parent::afterLoopItemActions($post);

        $break_point = $this->getBreakpoint();
        $current_index = $this->wp_query->current_post;
        $max_items = $this->wp_query->post_count;
        if ($current_index === intval($break_point - 1) || $current_index === ($max_items - 1)) {
            echo '</div>';
        }
    }

    public function renderLoopItem($post)
    {
        if (is_null($this->contentGenerator)) {
            $break_point = $this->getBreakpoint();
            $current_index = $this->wp_query->current_post;

            if (in_array($current_index, array($break_point - 1, $break_point))) {
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
        array_push($args, $this->wp_query);

        return call_user_func_array($this->contentGenerator, $args);
    }
}
