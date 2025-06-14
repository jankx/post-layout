<?php

namespace Jankx\PostLayout\Layout;

if (!defined('ABSPATH')) {
    exit('Cheatin huh?');
}

use Jankx\PostLayout\PostLayout;

class Preset6 extends PostLayout
{
    const LAYOUT_NAME = 'preset-6';

    protected $supportColumns = true;

    public function get_name()
    {
        return static::LAYOUT_NAME;
    }

    public static function get_layout_label()
    {
        return sprintf(__('Preset %d', 'jankx'), 6);
    }

    public function is_pro()
    {
        return false;
    }

    public function beforeLoopItemActions($post)
    {
        if ($this->wp_query->current_post === 1) {
            $subListWrapAttributes = array(
                'class' => 'sub-list',
            );

            echo sprintf('<div %s>', jankx_generate_html_attributes($subListWrapAttributes));
        }
        return parent::beforeLoopItemActions($post);
    }

    public function afterLoopItemActions($post)
    {
        if ($this->wp_query->total_post < 2 || $this->wp_query->current_post < ($this->wp_query->post_count - 1)) {
            return parent::afterLoopItemActions($post);
        }
        parent::afterLoopItemActions($post);
        echo '<div>';
    }

    public function renderLoopItem($post)
    {
        if (is_null($this->contentGenerator)) {
            if ($this->wp_query->current_post === 0) {
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
}
