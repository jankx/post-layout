<?php
namespace Jankx\PostLayout\Data;

use Jankx;

class Post
{
    protected $uiFramework;
    protected $post;

    public function __construct($postID = null)
    {
        $this->uiFramework = Jankx::ui();

        global $post;
        if (!empty($postID)) {
            /**
             * Backup curernt post to temporary
             */
            $tmp = $post;

            /**
             * Get post from post arg when call Jankx::post()
             */
            $post = get_post($postID);

            /**
             * If have any error when get_post() Jankx will restore backup post variable from $tmp
             */
            if (empty($post)) {
                $post = $tmp;
            }
            setup_postdata($post);
        }

        $this->post = $post;
    }


    public function ID()
    {
        return ! empty($this->post) ? $this->post->ID : false;
    }

    public function title($before = '', $after = '', $echo = true)
    {
        $title = get_the_title();

        if (strlen($title) == 0) {
            return;
        }

        $title = $before . $title . $after;

        if ($echo) {
            echo $title;
        } else {
            return $title;
        }
    }

    public function thumbnail($size = 'post-thumbnail', $attrs = array(), $postID = null, $echo = true)
    {
        global $post;

        if (has_post_thumbnail()) {
            $thumbnail = get_the_post_thumbnail();
        } else {
            $thumbnail = '<div class="no-thumbnail"><span></span></div>';
        }
        if (!$echo) {
            return $thumbnail;
        }
        echo $thumbnail;
    }

    public function exceprt()
    {
        echo apply_filters('the_excerpt', get_the_excerpt());
    }

    public function date($d = '', $before = '', $after = '', $echo = true)
    {
        if (is_new_day()) {
            $the_date    = $before . get_the_date($d) . $after;
            /**
             * Filters the date a post was published for display.
             *
             * @since 0.71
             *
             * @param string $the_date The formatted date string.
             * @param string $d        PHP date format. Defaults to 'date_format' option
             *                         if not specified.
             * @param string $before   HTML output before the date.
             * @param string $after    HTML output after the date.
             */
            $the_date = apply_filters('the_date', $the_date, $d, $before, $after);

            if ($echo) {
                echo $the_date;
            } else {
                return $the_date;
            }
        }
    }

    public function author()
    {
        echo get_the_author_link();
    }

    public function format()
    {
        if (! $this->post) {
            return false;
        }

        if (! post_type_supports($this->post->post_type, 'post-formats')) {
            return false;
        }

        $_format = get_the_terms($this->post->ID, 'post_format');

        if (empty($_format)) {
            return false;
        }

        $format = reset($_format);

        return str_replace('post-format-', '', $format->slug);
    }

    public function permalink()
    {
        echo esc_url(
            apply_filters('the_permalink', get_permalink($this->post), $this->post)
        );
    }

    public function next()
    {
    }

    public function prev()
    {
    }

    public function isSticky()
    {
        $post_id = absint($post_id);

        if (! $post_id) {
            $post_id = get_the_ID();
        }

        $stickies = get_option('sticky_posts');

        if (! is_array($stickies)) {
            return false;
        }

        if (in_array($post_id, $stickies)) {
            return true;
        }

        return false;
    }

    public function type($post = null)
    {
        if ($post = get_post($post)) {
            return $post->post_type;
        }

        return false;
    }
}
