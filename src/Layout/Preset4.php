<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\PostLayout;

class Preset4 extends PostLayout
{
    const LAYOUT_NAME = 'preset-4';

    protected $supportColumns = false;

    public function get_name()
    {
        return static::LAYOUT_NAME;
    }

    public static function get_layout_label()
    {
        return sprintf(__('Preset %d', 'jankx'), 4);
    }

    public function is_pro()
    {
        return false;
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

    protected function beforeLoopItemActions($post)
    {
        $currentIndex = $this->wp_query->current_post;
        $totalPosts = $this->wp_query->post_count;

        if ($currentIndex === 1) {
            $listPostsTagWrapAttributes = array(
                'class' => array('sub-layout'),
            );
            printf('<div %s>', jankx_generate_html_attributes($listPostsTagWrapAttributes));
        }

        if ($totalPosts > 2) {
            if ($currentIndex === 2) {
                $bottomListTagWrapAttrs = array(
                    'class' => array('bottom-list')
                );
                printf('<div %s>', jankx_generate_html_attributes($bottomListTagWrapAttrs));
            }
        }
    }
    protected function afterLoopItemActions($post)
    {
        $currentIndex = $this->wp_query->current_post;
        $totalPosts = $this->wp_query->post_count;

        if ($currentIndex > 0 && $currentIndex === ($totalPosts - 1)) {
            if ($totalPosts > 2) {
                echo '</div> <!-- /.bottom-list -->';
            }
            echo '</div> <!-- /.sub-layout -->';
        }
    }
}
