<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\Constracts\PostLayoutParent;
use Jankx\PostLayout\PostLayout;
use Jankx\PostLayout\Layout\Tabs\Tab;

class Tabs extends PostLayout implements PostLayoutParent
{
    const LAYOUT_NAME = 'tabs';

    protected $supportColumns = false;
    protected $enableTabCarousel = false;

    protected $tabs = array();

    public function get_name()
    {
        return static::LAYOUT_NAME;
    }

    public static function get_layout_label()
    {
        return __('Tabs', 'jankx');
    }

    public function enableCarouselOnMobile()
    {
        $this->enableTabCarousel = true;
    }

    public function render($echo = true)
    {
        if (!$this->childLayout) {
            return;
        }
        $post_type = $this->wp_query->get('post_type');

        do_action("jankx/layout/tabs/{$post_type}/init", $this, $post_type);

        if (!$echo) {
            ob_start();
        }
        $wrapClass = array('jankx-parent-layout', 'tabs', sprintf('%s-layout', $this->get_name()));
        $wrapClass[] = is_array($post_type) ? array_map(function ($post_type) {
            return 'post-type-' . $post_type;
        }, $post_type) : sprintf('post-type-%s', $post_type);
        $attributes = array(
            'class' => $wrapClass,
            'id' => sprintf('post-%s-%s', $this->get_name(), $this->instanceId)
        );

        if ($this->enableTabCarousel) {
            $attributes['data-tab-carousel'] = true;
        }
        ?>
        <div <?php echo jankx_generate_html_attributes($attributes); ?>>
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

    public function setItemAsSplide()
    {
        $callback = array($this->childLayout, 'setItemAsSplide');
        if (isset($this->childLayout) && is_callable($callback)) {
            return call_user_func_array($callback, func_get_args());
        }
    }
}
