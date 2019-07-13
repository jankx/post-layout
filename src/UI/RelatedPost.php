<?php
namespace Jankx\PostLayouts\UI;


class RelatedPost
{
    public function __construct()
    {
        add_action('jankx_after_main_content', array($this, 'show'), 35);
    }

    public function show()
    {
        echo 'related post';
    }
}

