<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\PostLayout;

class Preset3 extends Carousel
{
    const LAYOUT_NAME = 'preset3';

    public function get_name()
    {
        return static::LAYOUT_NAME;
    }

    public function beforeLoop() {
        if ($this->checkNextPost()) {
            $this->the_post();
            $post = $this->getCurrentPostItem();

            $this->beforeLoopItemActions($post);
            $this->renderLoopItem($post);
            $this->afterLoopItemActions($post);
        }
        parent::beforeLoop();
    }
}
