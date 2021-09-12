<?php
namespace Jankx\PostLayout;

use WP_Term_Query;
use Jankx\TemplateEngine\Engine;
use Jankx\PostLayout\Constracts\TermLayout as TermLayoutConstract;
use Jankx\TemplateEngine\Data\Term;

abstract class TermLayout implements TermLayoutConstract
{
    protected $wp_term_query;
    protected $templateEngine;
    protected $supportColumns = false;
    protected $mode = 'replace';
    protected $options = array();

    public $current_term;

    public function __construct($wp_term_query = null)
    {
        if (!is_null($wp_term_query)) {
            $this->set_term_query($wp_term_query);
        }
    }

    public function set_term_query($wp_term_query)
    {
        if (is_a($wp_term_query, WP_Term_Query::class)) {
            $this->wp_term_query = $wp_term_query;
        }
    }

    public function setTemplateEngine($engine)
    {
        if (is_a($engine, Engine::class)) {
            $this->templateEngine = $engine;
        }
    }

    public function setOptions($options)
    {
        // Parse post layout with default options
        $this->options =  apply_filters(
            "jankx_post_layout_{$this::get_name()}_set_options",
            wp_parse_args(
                $options,
                $this->options
            ),
            $this
        );
    }

    public function getOptions()
    {
        return (array)$this->options;
    }

    public function getOption($optionName, $defaultValue = null)
    {
        if (isset($this->options[$optionName])) {
            return $this->options[$optionName];
        }
        return $defaultValue;
    }

    protected function prepareTemplateData($data)
    {
        $data = array_merge(array(
        ), $data);

        return $data;
    }

    protected function createWrapAttributes()
    {
        $attributes = array(
            'class' => array('jankx-post-layout-wrap'),
        );
        return $attributes;
    }

    public function postLayoutStart($disableWTopWrapper = false)
    {
        if (!$disableWTopWrapper) {
            echo '<div ' . jankx_generate_html_attributes($this->createWrapAttributes()) . '>';
        }

        $taxonomies = (array)$this->wp_term_query->query_vars['taxonomy'];
        $postsListClasses = array_merge(
            array('jankx-posts', sprintf('%s-layout', $this->get_name())),
            array_map(function ($taxonomie) {
                return 'taxonomy-' . $taxonomie;
            }, $taxonomies)
        );

        if ($this->supportColumns && !empty($this->options['columns'])) {
            $postsListClasses[] = 'columns-' . $this->options['columns'];
        }

        $attributes = array(
            'class' => $postsListClasses,
            'data-mode' => $this->mode,
        );

        echo '<div ' . jankx_generate_html_attributes($attributes) . '>';
    }

    public function postLayoutEnd($disableWTopWrapper = false)
    {
        // Close posts list wrapper
        echo '</div><!-- End .jankx-posts -->';
        if (!$disableWTopWrapper) {
            echo '</div><!-- End .jankx-post-layout-wrap -->';
        }
    }

    protected function beforeLoopItemActions($term)
    {
        do_action('jankx/layout/term/loop/item/before', $term, $this->wp_term_query, $this);
    }

    protected function afterLoopItemActions($term)
    {
        do_action('jankx/layout/term/loop/item/after', $term, $this->wp_term_query, $this);
    }

    public function renderLoopItem($term)
    {
        return $this->templateEngine->render(
            array(
                $term->taxonomy . '-layout/' . $this->get_name() . '/loop-item',
                "post-layout/{$this->get_name()}/loop-item",
                'post-layout/term-item'
            ),
            $this->prepareTemplateData(array(
                'term' => new Term($term),
            ))
        );
    }

    public function render($echo = false)
    {
        if (!is_a($this->wp_term_query, WP_Term_Query::class)) {
            return;
        }
        $terms = $this->wp_term_query->get_terms();

        if (!$echo) {
            ob_start();
        }

        if (!empty($terms)) {
            $this->postLayoutStart();


            foreach ($terms as $term) {
                $this->current_term = $term;

                $this->beforeLoopItemActions($term);
                $this->renderLoopItem($term);
                $this->afterLoopItemActions($term);
            }

            $this->current_term = null;

            $this->postLayoutEnd();
        }


        if (!$echo) {
            return ob_get_clean();
        }
    }
}
