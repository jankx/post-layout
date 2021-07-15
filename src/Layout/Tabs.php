<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\Constracts\PostLayoutParent;
use Jankx\PostLayout\PostLayout;
use Jankx\PostLayout\Layout\Tabs\Tab;

class Tabs extends PostLayout implements PostLayoutParent
{
    const LAYOUT_NAME = 'tabs';

    protected $tabs = array();

    public function get_name()
    {
        return static::LAYOUT_NAME;
    }

    public static function get_layout_label()
    {
        return __('Tabs', 'jankx');
    }

    public function render($echo = true)
    {
        if (!$echo) {
            ob_start();
        }
        ?>
        <div class="jankx-parent-layout tabs">
        <?php
            $this->templateEngine->render(
                'post-layout/tabs/tabs',
                array(
                    'tabs' => $this->tabs,
                    'first_tab' => array_get($this->tabs, 0, []),
                    'tab_content' => $this->childLayout->render(false)
                )
            );
        ?>
        </div>
        <?php
        if (!$echo) {
            return ob_get_clean();
        }
    }

    public function addTab($tab_title, $tab_object, $url = null)
    {
        array_push(
            $this->tabs,
            new Tab($tab_title, $tab_object, $url)
        );
    }

    public function addTabs($tabs)
    {
        foreach ($tabs as $tab) {
            if (!isset($tab['title'], $tab['object'])) {
                error_log(sprintf('Jankx post layout has skipped tab: %s', json_encode($tab)));
                continue;
            }
            $this->addTab($tab['title'], $tab['object'], array_get($tab, 'url'));
        }
    }

    public function setContentGenerator($generator)
    {
        if (isset($this->childLayout)) {
            $this->childLayout->setContentGenerator($generator);
        } else {
            parent::setContentGenerator($generator);
        }
    }
}
