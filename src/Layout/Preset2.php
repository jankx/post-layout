<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\PostLayout;
use Jankx\PostLayout\Constracts\PostLayoutChildren;

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

    protected function beforeLoopItemActions($post)
    {
        $posts_per_page = $this->wp_query->get('posts_per_page');
        $break_point = ceil($posts_per_page/2);
        $current_index = $this->wp_query->current_post;

        if (in_array($current_index, array(0, $break_point))) {
            echo '<div class="preset-column column-'. $this->currentPresetColumnIndex .'">';

            $this->currentPresetColumnIndex += 1;
        }

        parent::beforeLoopItemActions($post);
    }

    protected function afterLoopItemActions($post)
    {
        parent::afterLoopItemActions($post);

        $posts_per_page = $this->wp_query->get('posts_per_page');
        $break_point = ceil($posts_per_page/2);
        $current_index = $this->wp_query->current_post;
        $max_items = $this->wp_query->post_count;
        if ($current_index === intval($break_point - 1) || $current_index === ($max_items -1)) {
            echo '</div>';
        }
    }

    public function renderLoopItem($post)
    {
        if (is_null($this->contentGenerator)) {
            $posts_per_page = $this->wp_query->get('posts_per_page');
            $break_point = ceil($posts_per_page/2);
            $current_index = $this->wp_query->current_post;
            $max_items = $this->wp_query->post_count;

            if (in_array($current_index, array($break_point -1, $break_point))) {
                return $this->templateEngine->render(array(
                    $post->post_type . '-layout/preset2/large-item',
                    'post-layout/preset2/large-item',
                    'post-layout/large-item',
                ));
            }

            return $this->templateEngine->render(
                array(
                    $post->post_type . '-layout/carousel/loop-item',
                    "post-layout/{$this->get_name()}/loop-item",
                    'post-layout/loop-item'
                ),
                $this->prepareTemplateData()
            );
        }

        $args = $this->contentGeneratorArgs;
        array_push($args, $post);
        array_push($args, $wp_query);

        return call_user_func_array($this->contentGenerator, $args);
    }
}
