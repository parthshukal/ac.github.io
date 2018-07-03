<?php

/**
 * Plugin Name: WP Meta SEO
 * Plugin URI: http://www.joomunited.com/wordpress-products/wp-meta-seo
 * Description: WP Meta SEO is a plugin for WordPress to fill meta for content, images and main SEO info in a single view.
 * Version: 3.6.2
 * Text Domain: wp-meta-seo
 * Domain Path: /languages
 * Author: JoomUnited
 * Author URI: http://www.joomunited.com
 * License: GPL2
 */
/**
 * @copyright 2014  Joomunited  ( email : contact _at_ joomunited.com )
 *
 *  Original development of this plugin was kindly funded by Joomunited
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

if (version_compare(PHP_VERSION, '5.3', '<')) {
    if (!function_exists('wpmsDisablePlugin')) {
        function wpmsDisablePlugin()
        {
            if (current_user_can('activate_plugins') && is_plugin_active(plugin_basename(__FILE__))) {
                deactivate_plugins(__FILE__);
                unset($_GET['activate']);
            }
        }
    }

    if (!function_exists('wpmsShowError')) {
        function wpmsShowError()
        {
            echo '<div class="error"><p><strong>WP Meta SEO</strong>
 need at least PHP 5.3 version, please update php before installing the plugin.</p></div>';
        }
    }

    //Add actions
    add_action('admin_init', 'wpmsDisablePlugin');
    add_action('admin_notices', 'wpmsShowError');

    //Do not load anything more
    return;
}

//Include the jutranslation helpers
include_once('jutranslation' . DIRECTORY_SEPARATOR . 'jutranslation.php');
call_user_func(
    '\Joomunited\WPMetaSEO\Jutranslation\Jutranslation::init',
    __FILE__,
    'wp-meta-seo',
    'WP Meta SEO',
    'wp-meta-seo',
    'languages' . DIRECTORY_SEPARATOR . 'wp-meta-seo-en_US.mo'
);

if (!defined('WPMETASEO_PLUGIN_URL')) {
    /**
     * url to WP Meta SEO plugin
     */
    define('WPMETASEO_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (!defined('WPMETASEO_PLUGIN_DIR')) {
    /**
     * path to WP Meta SEO plugin
     */
    define('WPMETASEO_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('URL')) {
    /**
     * Site url
     */
    define('URL', get_site_url());
}

if (!defined('WPMSEO_VERSION')) {
    /**
     * plugin version
     */
    define('WPMSEO_VERSION', '3.6.2');
}

if (!defined('WPMS_CLIENTID')) {
    /**
     * default client id to connect to google application
     */
    define('WPMS_CLIENTID', '992432963228-t8pc9ph1i7afaqocnhjl9cvoovc9oc7q.apps.googleusercontent.com');
}

if (!defined('WPMS_CLIENTSECRET')) {
    /**
     * default client secret to connect to google application
     */
    define('WPMS_CLIENTSECRET', 'tyF4XICemXdORWX2qjyazfqP');
}

if (!defined('MPMSCAT_TITLE_LENGTH')) {
    /**
     * default meta title length
     */
    define('MPMSCAT_TITLE_LENGTH', 69);
}

if (!defined('MPMSCAT_DESC_LENGTH')) {
    /**
     * default meta description length
     */
    define('MPMSCAT_DESC_LENGTH', 156);
}

if (!defined('MPMSCAT_KEYWORDS_LENGTH')) {
    /**
     * default meta keywords length
     */
    define('MPMSCAT_KEYWORDS_LENGTH', 256);
}

if (!defined('WPMSEO_FILE')) {
    /**
     * path to this file
     */
    define('WPMSEO_FILE', __FILE__);
}


if (!defined('WPMSEO_ADDON_FILENAME')) {
    /**
     * WP Meta SEO addon file
     */
    define('WPMSEO_ADDON_FILENAME', 'wp-meta-seo-addon/wp-meta-seo-addon.php');
}

if (!defined('WPMSEO_TEMPLATE_BREADCRUMB')) {
    /**
     * Default template for breadcrumb
     */
    define(
        'WPMSEO_TEMPLATE_BREADCRUMB',
        '<span property="itemListElement" typeof="ListItem">
<span property="name">%htitle%</span><meta property="position" content="%position%"></span>'
    );
}
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
register_activation_hook(__FILE__, array('WpMetaSeo', 'pluginActivation'));

require_once(WPMETASEO_PLUGIN_DIR . 'inc/class.wp-metaseo.php');
add_action('init', array('WpMetaSeo', 'init'));
require_once(WPMETASEO_PLUGIN_DIR . 'inc/class.metaseo-sitemap.php');
$GLOBALS['metaseo_sitemap'] = new MetaSeoSitemap;
if (is_admin()) {
    require_once(WPMETASEO_PLUGIN_DIR . 'inc/class.metaseo-content-list-table.php');
    require_once(WPMETASEO_PLUGIN_DIR . 'inc/class.metaseo-image-list-table.php');
    require_once(WPMETASEO_PLUGIN_DIR . 'inc/class.metaseo-dashboard.php');
    require_once(WPMETASEO_PLUGIN_DIR . 'inc/class.metaseo-broken-link-table.php');
    require_once(WPMETASEO_PLUGIN_DIR . 'inc/class.metaseo-google-analytics.php');
    require_once(WPMETASEO_PLUGIN_DIR . 'inc/class.metaseo-admin.php');
    $GLOBALS['metaseo_admin'] = new MetaSeoAdmin;

    add_filter('wp_prepare_attachment_for_js', array('MetaSeoImageListTable', 'addMoreAttachmentSizes'), 10, 2);
    add_filter('image_size_names_choose', array('MetaSeoImageListTable', 'addMoreAttachmentSizesChoose'), 10, 1);
    add_filter('user_contactmethods', 'metaseoContactuser', 10, 1);

    /**
     * Custom field for user profile
     * @param $contactusers
     * @return mixed
     */
    function metaseoContactuser($contactusers)
    {
        $contactusers['mtwitter'] = __('Twitter username (without @)', 'wp-meta-seo');
        $contactusers['mfacebook'] = __('Facebook profile URL', 'wp-meta-seo');
        return $contactusers;
    }

    include_once(WPMETASEO_PLUGIN_DIR . 'inc/google_analytics/wpmsga.php');
} else {
    /**
     * Outputs the breadcrumb
     * @param bool $return Whether to return or echo the trail. (optional)
     * @param bool $reverse Whether to reverse the output or not. (optional)
     * @return string
     */
    function wpmsBreadcrumb($return = false, $reverse = false)
    {
        require_once(WPMETASEO_PLUGIN_DIR . 'inc/breadcrumb/class.metaseo-breadcrumb.php');
        $breadcrumb = new MetaSeoBreadcrumb;
        if ($breadcrumb !== null) {
            $breadcrumb->checkPosts();
            return $breadcrumb->breadcrumbDisplay($return, $reverse);
        }
        return '';
    }

    /*
    * shortcode for breadcrumb
    */
    add_shortcode('wpms_breadcrumb', 'wpmsBreadcrumbShortcode');
    /**
     * Create shortcode for breadcrumb
     * @param $params
     * @return string
     */
    function wpmsBreadcrumbShortcode($params)
    {
        $html = '';
        if (function_exists('wpmsBreadcrumbShortcode')) {
            $html .= '<div class="breadcrumbs" typeof="BreadcrumbList" vocab="https://schema.org/">';
            if (isset($params['reverse']) && $params['reverse'] == 1) {
                $html .= wpmsBreadcrumb(true, true);
            } else {
                $html .= wpmsBreadcrumb(true, false);
            }
            $html .= '</div>';
        }
        return $html;
    }

    /******** Check again and modify title, meta title, meta description before output ********/
    if (is_plugin_active('divi_layout_injector/divi_layout_injector.php')) {
        add_action('get_header', 'buffer_start');
    } else {
        add_action('template_redirect', 'buffer_start');
    }

    add_action('wp_head', 'bufferEnd');
    require_once(WPMETASEO_PLUGIN_DIR . 'inc/class.metaseo-front_end.php');
    $GLOBALS['metaseo_front'] = new MetaSeoFront;
    function buffer_start()
    {
        ob_start("wpmsCallback");
    }

    function bufferEnd()
    {
        ob_end_flush();
    }

    /**
     * @param $buffer
     * @return mixed
     */
    function wpmsCallback($buffer)
    {
        // modify buffer here, and then return the updated code
        global $wp_query;
        if (empty($wp_query->post->ID)) {
            return $buffer;
        }

        // get meta title
        $meta_title = get_post_meta($wp_query->post->ID, '_metaseo_metatitle', true);
        if ($meta_title != maybe_unserialize($meta_title)) {
            $meta_title = '';
        }

        if ($meta_title == '') {
            $meta_title = $wp_query->post->post_title;
        }

        $meta_title_esc = esc_attr($meta_title);

        // get meta keyword
        $settings = get_option('_metaseo_settings');
        if (isset($settings['metaseo_showkeywords']) && $settings['metaseo_showkeywords'] == 1) {
            $meta_keywords = get_post_meta($wp_query->post->ID, '_metaseo_metakeywords', true);
            $meta_keywords_esc = esc_attr($meta_keywords);
        } else {
            $meta_keywords_esc = '';
        }
        $page_follow = get_post_meta($wp_query->post->ID, '_metaseo_metafollow', true);
        $page_index = get_post_meta($wp_query->post->ID, '_metaseo_metaindex', true);

        // get meta description
        $meta_description = get_post_meta($wp_query->post->ID, '_metaseo_metadesc', true);
        if ($meta_description != maybe_unserialize($meta_description)) {
            $meta_description = '';
        }

        if ($meta_description == '') {
            $content = strip_shortcodes($wp_query->post->post_content);
            $content = trim(strip_tags($content));
            if (strlen($content) > 156) {
                $meta_description = substr($content, 0, 152) . ' ...';
            } else {
                $meta_description = $content;
            }
        }
        $meta_desc_esc = esc_attr($meta_description);

        // get option reading
        $mpage_for_posts = get_option('page_for_posts');
        $mpage_on_front = get_option('page_on_front');
        $mshow_on_front = get_option('show_on_front');

        // check homepage is a page
        if (get_post_meta($wp_query->post->ID, '_metaseo_metatitle', true) == '' && is_front_page()) {
            $meta_title_esc = esc_attr($settings['metaseo_title_home']);
            if ($meta_title_esc != maybe_unserialize($meta_title_esc)) {
                $meta_title_esc = '';
            }
        }

        if (get_post_meta($wp_query->post->ID, '_metaseo_metadesc', true) == '' && is_front_page()) {
            $meta_desc_esc = esc_attr($settings['metaseo_desc_home']);
            if ($meta_desc_esc != maybe_unserialize($meta_desc_esc)) {
                $meta_desc_esc = '';
            }
        }

        // get meta title for twitter
        $twitter_title = get_post_meta($wp_query->post->ID, '_metaseo_metatwitter-title', true);
        if ($twitter_title != maybe_unserialize($twitter_title)) {
            $twitter_title = '';
        }

        $meta_twtitle = esc_attr($twitter_title);
        if ($meta_twtitle == '') {
            $meta_twtitle = $meta_title_esc;
        }


        // get meta description for twitter
        $twitter_desc = get_post_meta($wp_query->post->ID, '_metaseo_metatwitter-desc', true);
        if ($twitter_desc != maybe_unserialize($twitter_desc)) {
            $twitter_desc = '';
        }

        $meta_twdesc = esc_attr($twitter_desc);
        if ($meta_twdesc == '') {
            $meta_twdesc = $meta_desc_esc;
        }

        $sitename = get_bloginfo('name');
        $meta_twitter_site = get_user_meta($wp_query->post->post_author, 'mtwitter', true);
        $facebook_admin = get_user_meta($wp_query->post->post_author, 'mfacebook', true);

        $settings = get_option('_metaseo_settings');
        if ($settings) {
            if ($meta_twitter_site == '' && $settings['metaseo_showtwitter'] != '') {
                $meta_twitter_site = $settings['metaseo_showtwitter'];
            }

            if ($facebook_admin == '' && $settings['metaseo_showfacebook'] != '') {
                $facebook_admin = $settings['metaseo_showfacebook'];
            }
        }

        if ((!empty($settings['metaseo_twitter_card']))) {
            $meta_twcard = $settings['metaseo_twitter_card'];
        } else {
            $meta_twcard = 'summary';
        }
        $meta_twimage = get_post_meta($wp_query->post->ID, '_metaseo_metatwitter-image', true);

        // get meta title for facebook
        $meta_fbtitle = get_post_meta($wp_query->post->ID, '_metaseo_metaopengraph-title', true);
        if ($meta_fbtitle != maybe_unserialize($meta_fbtitle)) {
            $meta_fbtitle = '';
        }

        if ($meta_fbtitle == '') {
            $meta_fbtitle = $meta_title_esc;
        }

        // get meta description for facebook
        $meta_fbdesc = get_post_meta($wp_query->post->ID, '_metaseo_metaopengraph-desc', true);
        if ($meta_fbdesc != maybe_unserialize($meta_fbdesc)) {
            $meta_fbdesc = '';
        }

        if ($meta_fbdesc == '') {
            $meta_fbdesc = $meta_desc_esc;
        }

        $meta_fbimage = get_post_meta($wp_query->post->ID, '_metaseo_metaopengraph-image', true);

        $default_image = wp_get_attachment_image_src(get_post_thumbnail_id(1), 'single-post-thumbnail');
        if (empty($meta_twimage) && isset($default_image[0])) {
            $meta_twimage = $default_image[0];
        }

        if (empty($meta_fbimage) && isset($default_image[0])) {
            $meta_fbimage = $default_image[0];
        }

        // check homepage is latest post
        if (is_home()) {
            if ($mshow_on_front == 'posts') {
                $titlehome = esc_attr($settings['metaseo_title_home']);
                $deschome = esc_attr($settings['metaseo_desc_home']);
                if ($titlehome != maybe_unserialize($titlehome)) {
                    $titlehome = '';
                }

                if ($deschome != maybe_unserialize($deschome)) {
                    $deschome = '';
                }

                $meta_title = $meta_title_esc = $meta_twtitle = $meta_fbtitle = $titlehome;
                $meta_desc_esc = $meta_twdesc = $meta_fbdesc = $deschome;

                // set meta title when setting is empty
                if ($settings['metaseo_title_home'] == '') {
                    $meta_title = esc_attr(get_bloginfo('name') . ' - ' . get_bloginfo('description'));
                    $meta_title_esc = esc_attr(get_bloginfo('name') . ' - ' . get_bloginfo('description'));
                    $meta_twtitle = esc_attr(get_bloginfo('name') . ' - ' . get_bloginfo('description'));
                    $meta_fbtitle = esc_attr(get_bloginfo('name') . ' - ' . get_bloginfo('description'));
                }

                // set meta description when setting is empty
                if ($settings['metaseo_desc_home'] == '') {
                    $meta_desc_esc = esc_attr(get_bloginfo('description'));
                    $meta_twdesc = esc_attr(get_bloginfo('description'));
                    $meta_fbdesc = esc_attr(get_bloginfo('description'));
                }
                $page_follow = 'follow';
                $page_index = 'index';
            } elseif ($mshow_on_front == 'page') { // is page posts
                $meta_title = esc_attr(get_post_meta($mpage_for_posts, '_metaseo_metatitle', true));
                $meta_title_esc = esc_attr(get_post_meta($mpage_for_posts, '_metaseo_metatitle', true));
                $meta_twtitle = esc_attr(get_post_meta($mpage_for_posts, '_metaseo_metatitle', true));
                $meta_fbtitle = esc_attr(get_post_meta($mpage_for_posts, '_metaseo_metatitle', true));
                $page_follow = get_post_meta($mpage_for_posts, '_metaseo_metafollow', true);
                $page_index = get_post_meta($mpage_for_posts, '_metaseo_metaindex', true);
            }
        }

        // is front page
        if (is_front_page() && 'page' == get_option('show_on_front') && is_page(get_option('page_on_front'))) {
            $meta_title = esc_attr(get_post_meta($mpage_on_front, '_metaseo_metatitle', true));
            $meta_title_esc = esc_attr(get_post_meta($mpage_on_front, '_metaseo_metatitle', true));
            $meta_twtitle = esc_attr(get_post_meta($mpage_on_front, '_metaseo_metatitle', true));
            $meta_fbtitle = esc_attr(get_post_meta($mpage_on_front, '_metaseo_metatitle', true));

            $meta_desc_esc = esc_attr(get_post_meta($mpage_on_front, '_metaseo_metadesc', true));
            $meta_twdesc = esc_attr(get_post_meta($mpage_on_front, '_metaseo_metadesc', true));
            $meta_fbdesc = esc_attr(get_post_meta($mpage_on_front, '_metaseo_metadesc', true));
            $page_follow = get_post_meta($mpage_on_front, '_metaseo_metafollow', true);
            $page_index = get_post_meta($mpage_on_front, '_metaseo_metaindex', true);
        }


        if (is_category() || is_tag() || is_tax()) {
            $term = $wp_query->get_queried_object();
            if (is_object($term) && !empty($term)) {
                if (function_exists('get_term_meta')) {
                    $cat_metatitle = get_term_meta($term->term_id, 'wpms_category_metatitle', true);
                    $cat_metadesc = get_term_meta($term->term_id, 'wpms_category_metadesc', true);
                } else {
                    $cat_metatitle = get_metadata('term', $term->term_id, 'wpms_category_metatitle', true);
                    $cat_metadesc = get_metadata('term', $term->term_id, 'wpms_category_metadesc', true);
                }

                if (isset($settings['metaseo_showkeywords']) && $settings['metaseo_showkeywords'] == 1) {
                    if (function_exists('get_term_meta')) {
                        $meta_keywords = get_term_meta($term->term_id, 'wpms_category_metakeywords', true);
                    } else {
                        $meta_keywords = get_metadata('term', $term->term_id, 'wpms_category_metakeywords', true);
                    }

                    $meta_keywords_esc = esc_attr($meta_keywords);
                } else {
                    $meta_keywords_esc = '';
                }

                if (isset($cat_metatitle) && $cat_metatitle != '') {
                    $meta_title = $meta_title_esc = $meta_fbtitle = $meta_twtitle = esc_attr($cat_metatitle);
                } else {
                    $meta_title = $meta_title_esc = $meta_fbtitle = $meta_twtitle = esc_attr($term->name);
                }

                if (isset($cat_metadesc) && $cat_metadesc != '') {
                    $meta_desc_esc = $meta_fbdesc = $meta_twdesc = esc_attr($cat_metadesc);
                } else {
                    $meta_desc_esc = $meta_fbdesc = $meta_twdesc = esc_attr($term->description);
                }
            } else {
                $meta_title = $meta_title_esc = $meta_fbtitle = $meta_twtitle = '';
                $meta_desc_esc = $meta_fbdesc = $meta_twdesc = '';
            }

            $page_follow = 'follow';
        }

        if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) {
            $http = 'https';
        } else {
            $http = 'http';
        }
        $current_url = $http . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $current_url = esc_url($current_url);

        if (is_front_page() || is_home()) {
            $type = 'website';
        } elseif (is_singular()) {
            $type = 'article';
        } else {
            // We use "object" for archives etc. as article doesn't apply there.
            $type = 'object';
        }

        /* for facebook application id */
        $fbapp_id = $settings['metaseo_showfbappid'];
        // create List array meta tag
        $patterns = array(
            'twitter_image' => array(
                '#<meta name="twitter:image" [^<>]+ ?>#i',
                '<meta name="twitter:image" content="' . $meta_twimage . '" />',
                ($meta_twimage != '' ? true : false)),
            'twitter_card' => array(
                '#<meta name="twitter:card" [^<>]+ ?>#i',
                '<meta name="twitter:card" content="' . $meta_twcard . '" />',
                ($meta_twcard != '' ? true : false)),
            'twitter_site' => array(
                '#<meta name="twitter:site" [^<>]+ ?>#i',
                '<meta name="twitter:site" content="@' . $meta_twitter_site . '" />',
                ($meta_twitter_site != '' ? true : false)),
            'twitter_domain' => array(
                '#<meta name="twitter:domain" [^<>]+ ?>#i',
                '<meta name="twitter:domain" content="' . $sitename . '" />',
                ($sitename != '' ? true : false)),
            'twitter_desc' => array(
                '#<meta name="twitter:description" [^<>]+ ?>#i',
                '<meta name="twitter:description" content="' . $meta_twdesc . '" />',
                ($meta_twdesc != '' ? true : false)),
            'twitter_title' => array(
                '#<meta name="twitter:title" [^<>]+ ?>#i',
                '<meta name="twitter:title" content="' . $meta_twtitle . '" />',
                ($meta_twtitle != '' ? true : false)),
            '_title' => array('/<title.*?\/title>/i', '<title>' . $meta_title . '</title>',
                ($meta_title != '' ? true : false)),
            'facebook_admin' => array(
                '#<meta property="fb:admins" [^<>]+ ?>#i',
                '<meta property="fb:admins" content="' . $facebook_admin . '" />',
                ($facebook_admin != '' ? true : false)),
            'facebook_image' => array(
                '#<meta property="og:image" [^<>]+ ?>#i',
                '<meta property="og:image" content="' . $meta_fbimage . '" />',
                ($meta_fbimage != '' ? true : false)),
            'site_name' => array(
                '#<meta property="og:site_name" [^<>]+ ?>#i',
                '<meta property="og:site_name" content="' . $sitename . '" />',
                ($sitename != '' ? true : false)),
            'og:description' => array(
                '#<meta property="og:description" [^<>]+ ?>#i',
                '<meta property="og:description" content="' . $meta_fbdesc . '" />',
                ($meta_fbdesc != '' ? true : false)),
            'og:url' => array(
                '#<meta property="og:url" [^<>]+ ?>#i',
                '<meta property="og:url" content="' . $current_url . '" />',
                ($current_url != '' ? true : false)),
            'og:type' => array(
                '#<meta property="og:type" [^<>]+ ?>#i',
                '<meta property="og:type" content="' . $type . '" />',
                ($type != '' ? true : false)),
            'fb:app_id' => array(
                '#<meta property="fb:app_id" [^<>]+ ?>#i',
                '<meta property="fb:app_id" content="' . $fbapp_id . '" />',
                ($type != '' ? true : false)),
            'og:title' => array(
                '#<meta property="og:title" [^<>]+ ?>#i',
                '<meta property="og:title" content="' . $meta_fbtitle . '" />',
                ($meta_fbtitle != '' ? true : false)),
            '_description' => array(
                '#<meta name="description" [^<>]+ ?>#i',
                '<meta name="description" content="' . $meta_desc_esc . '" />',
                ($meta_desc_esc != '' ? true : false)),
            'keywords' => array(
                '#<meta name="keywords" [^<>]+ ?>#i',
                '<meta name="keywords" content="' . $meta_keywords_esc . '" />',
                ($meta_keywords_esc != '' ? true : false)),
            'title' => array(
                '#<meta name="title" [^<>]+ ?>#i',
                '<meta name="title" content="' . $meta_title_esc . '" />',
                ($meta_title_esc != '' ? true : false))
        );

        if (empty($page_index)) {
            $page_index = 'index';
        }

        if (empty($page_follow)) {
            $page_follow = 'follow';
        }

        if (!empty($settings['metaseo_follow'])) {
            $patterns['follow'] = array(
                '#<meta name="robots" [^<>]+ ?>#i',
                '<meta name="robots" content="' . $page_index . ',' . $page_follow . '" />'
            );
        }

        if (get_post_meta($wp_query->post->ID, '_metaseo_metatitle', true) != '') {
            $patterns['title'] = array(
                '#<meta name="title" [^<>]+ ?>#i',
                '<meta name="title" content="' . $meta_title_esc . '" />',
                ($meta_title_esc != '' ? true : false));
        }

        // unset meta tag if empty value
        if ($meta_keywords_esc == '') {
            unset($patterns['keywords']);
        }

        if (!isset($fbapp_id) || (isset($fbapp_id) && $fbapp_id == '')) {
            unset($patterns['fb:app_id']);
        }

        if ($meta_twitter_site == '') {
            unset($patterns['twitter_site']);
        }

        if ($meta_twimage == '') {
            unset($patterns['twitter_image']);
        }

        if ($meta_twtitle == '') {
            unset($patterns['twitter_title']);
        }

        if ($meta_twdesc == '') {
            unset($patterns['twitter_desc']);
        }

        if ($meta_fbdesc == '') {
            unset($patterns['og:description']);
        }

        if ($meta_desc_esc == '') {
            unset($patterns['_description']);
        }

        if ($facebook_admin == '') {
            unset($patterns['facebook_admin']);
        }

        if ($meta_fbimage == '') {
            unset($patterns['facebook_image']);
        }

        $default_settings = array(
            "metaseo_title_home" => "",
            "metaseo_desc_home" => "",
            "metaseo_showfacebook" => "",
            "metaseo_showtwitter" => "",
            "metaseo_twitter_card" => "summary",
            "metaseo_showkeywords" => 0,
            "metaseo_showtmetablock" => 1,
            "metaseo_showsocial" => 1,
            "metaseo_metatitle_tab" => 0
        );

        if (is_array($settings)) {
            $default_settings = array_merge($default_settings, $settings);
        }

        if (empty($default_settings['metaseo_metatitle_tab'])) {
            unset($patterns['_title']);
        }

        // unset meta tag if empty value
        if ((isset($default_settings['metaseo_showsocial']) && $default_settings['metaseo_showsocial'] == 0)) {
            unset($patterns['twitter_image']);
            unset($patterns['twitter_card']);
            unset($patterns['twitter_site']);
            unset($patterns['twitter_domain']);
            unset($patterns['twitter_desc']);
            unset($patterns['twitter_title']);
            unset($patterns['facebook_admin']);
            unset($patterns['facebook_image']);
            unset($patterns['site_name']);
            unset($patterns['og:description']);
            unset($patterns['og:title']);
        }

        foreach ($patterns as $k => $pattern) {
            if (preg_match_all($pattern[0], $buffer, $matches)) {
                $replacement = array();
                foreach ($matches[0] as $key => $match) {
                    if ($key < 1) {
                        $replacement[] = $pattern[2] ? $pattern[1] : $match . "\n";
                    } else {
                        $replacement[] = '';
                    }
                }

                $buffer = str_ireplace($matches[0], $replacement, $buffer);
            } else {
                $buffer = str_ireplace('</title>', "</title>\n" . $pattern[1], $buffer);
            }
        }

        return $buffer;
    }

    /*     * ******************************************** */
}

/* * ****** Check and import meta data from other installed plugins for SEO ******* */

/**
 * Handle import of meta data from other installed plugins for SEO
 *
 * @since 1.5.0
 */
function wpmsYoastMessage()
{
    $activated = 0;
    // Check if All In One Pack is active
    if (!get_option('_aio_import_notice_flag')) {
        if (is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php')) {
            add_action('admin_notices', 'wpmsImportMetaNotice', 2);
            $activated++;
        }

        if (get_option('_aio_import_notice_flag') === false) {
            update_option('_aio_import_notice_flag', 0);
        }
    }
    // Check if Yoast is active
    if (!get_option('_yoast_import_notice_flag', false)) {
        if (is_plugin_active('wordpress-seo/wp-seo.php')
            || is_plugin_active('Yoast-SEO-Premium/wp-seo-premium.php') || class_exists('WPSEO_Premium')) {
            add_action('admin_notices', 'wpmsImportYoastMetaNotice', 3);
            $activated++;
        }

        if (get_option('_yoast_import_notice_flag') === false) {
            update_option('_yoast_import_notice_flag', 0);
        }
    }

    if ($activated === 2 && !get_option('plugin_to_sync_with', false)) {
        add_action(
            'admin_notices',
            create_function(
                '$notImportant',
                'echo "<div class=\"error metaseo-import-wrn\"><p>". __("Be careful you installed 2 extensions doing
 almost the same thing, please deactivate AIOSEO or
  Yoast in order to work more clearly!", "wp-meta-seo") ."</p></div>";'
            ),
            1
        );
    }
}

add_action('admin_init', 'wpmsYoastMessage');

function wpmsImportMetaNotice()
{
    echo '<div class="error metaseo-import-wrn"><p>' . sprintf(__('We have found that you&#39;re using
     All In One Pack Plugin, WP Meta SEO can import the meta
      from this plugin, %s', 'wp-meta-seo'), '<a href="#" class="button mseo-import-action"
 style="position:relative" onclick="importMetaData(this, event)" id="_aio_">
 <span class="spinner-light"></span>Import now</a> or
  <a href="#" class="dissmiss-import">dismiss this</a>') . '</p></div>';
}

function wpmsImportYoastMetaNotice()
{
    echo '<div class="error metaseo-import-wrn"><p>' . sprintf(__('We have found that you&#39;re using Yoast SEO Plugin,
     WP Meta SEO can import the meta from this plugin, %s', 'wp-meta-seo'), '<a href="#"
 class="button mseo-import-action" style="position:relative"
  onclick="importMetaData(this, event)" id="_yoast_">Import now<span class="spinner-light"></span></a>
   or <a href="#" class="dissmiss-import">dismiss this</a>') . '</p></div>';
}

/**
 * Encode or decode all values in string format of an array
 * @param $obj
 * @param string $action
 * @return mixed
 */
function wpmsUtf8($obj, $action = 'encode')
{
    $action = strtolower(trim($action));
    $fn = "utf8_$action";
    if (is_array($obj)) {
        foreach ($obj as &$el) {
            if (is_array($el)) {
                if (is_callable($fn)) {
                    $el = wpmsUtf8($el, $action);
                }
            } elseif (is_string($el)) {
                $isASCII = mb_detect_encoding($el, 'ASCII');
                if ($action === 'encode' && !$isASCII) {
                    $el = mb_convert_encoding($el, "UTF-8", "auto");
                }

                $el = $fn($el);
            }
        }
    } elseif (is_object($obj)) {
        $vars = array_keys(get_object_vars($obj));
        foreach ($vars as $var) {
            wpmsUtf8($obj->$var, $action);
        }
    }

    return $obj;
}

/**
 * @param $field
 * @param $meta
 */
function textLink($field, $meta)
{
    echo '<input class="cmb_text_link" type="text" size="45" id="', $field['id'], '"
     name="', $field['id'], '" value="', $meta, '" />';
    echo '<input class="cmb_link_button button" type="button"
 value="Voeg link toe" />', '<p class="cmb_metabox_description">', $field['desc'], '</p>';
}

add_action('cmb_render_text_link', 'textLink', 10, 2);
add_action('template_redirect', 'wpmsTemplateRedirect');

/**
 * Redirect 404 url and insert url to database
 */
function wpmsTemplateRedirect()
{
    global $wpdb;
    if (is_404()) {
        if (isset($_SERVER['REQUEST_URI'])) {
            $url = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
            $sql = $wpdb->prepare(
                "SELECT * FROM " . $wpdb->prefix . "wpms_links WHERE (link_url=%s OR link_url=%s)",
                array(
                    $url,
                    esc_url($url)
                )
            );
            $check = $wpdb->get_results($sql);
            if (count($check) == 0) {
                // insert url
                $insert = array(
                    'link_url' => ($url),
                    'status_code' => '404 Not Found',
                    'status_text' => '404 Not Found',
                    'type' => '404_automaticaly',
                    'broken_indexed' => 1,
                    'broken_internal' => 0,
                    'warning' => 0,
                    'dismissed' => 0
                );

                $wpdb->insert($wpdb->prefix . 'wpms_links', $insert);
            } else {
                // update url
                $sql = $wpdb->prepare(
                    "SELECT * FROM " . $wpdb->prefix . "wpms_links WHERE (link_url=%s OR link_url=%s) ",
                    array(
                        $url,
                        esc_url($url)
                    )
                );
                $links_broken = $wpdb->get_row($sql);
                if (!empty($links_broken)) {
                    $value = array('hit' => (int)$links_broken->hit + 1);
                    $wpdb->update(
                        $wpdb->prefix . 'wpms_links',
                        $value,
                        array('id' => $links_broken->id),
                        array('%d'),
                        array('%d')
                    );

                    if (($url == $links_broken->link_url || esc_url($url) == $links_broken->link_url)
                        && $links_broken->link_url_redirect != '') {
                        if ($links_broken->type == 'add_custom') {
                            $status_redirect = $links_broken->meta_title;
                        } else {
                            $status_redirect = 302;
                        }
                        if (empty($status_redirect)) {
                            $status_redirect = 302;
                        }
                        wp_redirect($links_broken->link_url_redirect, $status_redirect);
                        exit();
                    }
                }
            }
        }

        $defaul_settings_404 = array(
            'wpms_redirect_homepage' => 0,
            'wpms_type_404' => 'none',
            'wpms_page_redirected' => 'none'
        );
        $wpms_settings_404 = get_option('wpms_settings_404');
        if (is_array($wpms_settings_404)) {
            $defaul_settings_404 = array_merge($defaul_settings_404, $wpms_settings_404);
        }

        // redirect url by settings
        if (isset($defaul_settings_404['wpms_redirect_homepage'])
            && $defaul_settings_404['wpms_redirect_homepage'] == 1) {
            wp_redirect(get_home_url());
            exit();
        } else {
            if (isset($defaul_settings_404['wpms_type_404'])) {
                switch ($defaul_settings_404['wpms_type_404']) {
                    case 'wp-meta-seo-page':
                        global $wpdb;
                        $sql = $wpdb->prepare(
                            "SELECT * FROM " . $wpdb->prefix . "posts WHERE post_title = %s AND post_excerpt = %s",
                            array(
                                "WP Meta SEO 404 Page",
                                "metaseo_404_page"
                            )
                        );
                        $wpms_page = $wpdb->get_row($sql);
                        if (!empty($wpms_page)) {
                            $link_redirect = get_permalink($wpms_page->ID);
                            if ($link_redirect) {
                                wp_redirect($link_redirect);
                                exit();
                            }
                        }
                        break;

                    case 'custom_page':
                        if (isset($defaul_settings_404['wpms_page_redirected'])
                            && $defaul_settings_404['wpms_page_redirected'] != 'none') {
                            $link_redirect = get_permalink($defaul_settings_404['wpms_page_redirected']);
                            if ($link_redirect) {
                                wp_redirect($link_redirect);
                                exit();
                            }
                        }
                        break;
                }
            }
        }
    }

    // redirect by rule
    if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
        $url = $_SERVER['REQUEST_URI'];
        $matches = false;
        $all_links = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "wpms_links");
        $target = '';
        $status_redirect = 302;
        foreach ($all_links as $link) {
            if ($link->link_url == '/') {
                continue;
            }
            $link->link_url = str_replace('/*', '/(.*)', $link->link_url);
            if ((@preg_match('@' . str_replace('@', '\\@', $link->link_url) . '@', $url, $matches) > 0) || (@preg_match('@' . str_replace('@', '\\@', $link->link_url) . '@', urldecode($url), $matches) > 0)) {    // Check if our match wants this URL
                $target = $link->link_url_redirect;
                if ($link->type == 'add_custom') {
                    $status_redirect = $link->meta_title;
                }
                break;
            }
        }

        if (!empty($target)) {
            if (empty($status_redirect)) {
                $status_redirect = 302;
            }
            wp_redirect($target, $status_redirect);
            exit();
        }
    }
}
