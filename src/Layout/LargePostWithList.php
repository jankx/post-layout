<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\PostLayout;

class LargePostWithList extends PostLayout
{
    public function render()
    {
        $args = array_merge(
            $this->options,
            array(
                'wp_query' => $this->wp_query
            )
        );

        return jankx_template(
            sprintf('layout/posts/%1$s-large-post-%2$s-list', 'left', 'right'),
            $args
        );
    }
}
