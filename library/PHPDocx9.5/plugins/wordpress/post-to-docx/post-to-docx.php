<?php
/*
Plugin Name: Post to docx
Plugin URI: http://www.phpdocx.com
Description: Allow visitor to download post in DOCX format.
Version: 1.0
Author: 2mdc
Author URI: http://www.2mdc.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/
//avoid direct calls to this file, because now WP core and framework has been used
if (!function_exists('add_action')) {
    echo 'I\'m just a plugin, not much I can do when called directly.';
    exit();
}

// Define certain terms which may be required throughout the plugin
global $blog_id;
define('WPPT0DOCX_NAME', 'WP Post to DOCX');
define('WPPT0DOCX_SNAME', 'posttodocx');
define('WPPT0DOCX_PATH', WP_PLUGIN_DIR . '/post-to-docx');
define('WPPT0DOCX_URL', WP_PLUGIN_URL . '/post-to-docx');
define('WPPT0DOCX_BASENAME', plugin_basename(__FILE__));

if (!class_exists(Posttodocx)) {

    class Posttodocx
    {

        private $options;

        function Posttodocx()
        {
            $this->options = get_option('posttodocx');
            if (is_admin()) {
                add_action('admin_init', array(&$this, 'on_admin_init'));
                add_action('admin_menu', array(&$this, 'on_admin_menu'));
                add_filter("plugin_action_links_" . WPPT0DOCX_BASENAME, array(&$this, 'action_links'));
                register_activation_hook(WPPT0DOCX_BASENAME, array(&$this, 'on_activate'));
               //add_action('post_updated', array(&$this, 'generate_docx_file'));
            } else {
                add_action('wp', array(&$this, 'generate_docx'));
                add_filter('the_content', array(&$this, 'add_button'));
            }
        }

        function on_admin_init()
        {
            register_setting('posttodocx_options', 'posttodocx', array(&$this, 'on_update_options'));
        }

        function on_update_options($post)
        {
            return $post;
        }

        function on_admin_menu()
        {
            $option_page = add_options_page('Post to DOCX Options', 'Post to DOCX', 'administrator', WPPT0DOCX_BASENAME, array(&$this, 'options_page'));
            add_action("admin_print_styles-$option_page", array(&$this, 'on_admin_print_styles'));
        }

        function options_page()
        {
            include(WPPT0DOCX_PATH . '/posttodocx_options.php');
        }

        function on_admin_print_styles()
        {
            wp_enqueue_style('posttodocxadminstyle', WPPT0DOCX_URL . '/asset/css/admin.css', false, '1.0', 'all');
        }

        function action_links($links)
        {
            $settings_link = '<a href="options-general.php?page=' . WPPT0DOCX_BASENAME . '">Settings</a>';
            array_unshift($links, $settings_link);
            return $links;
        }

        function generate_docx()
        {
            if ($_GET['format'] == 'docx') {
                if ($this->options['nonPublic'] and !is_user_logged_in()) {
                    return false;
                }

                global $post;

                $fileName = $post->post_name;
                $this->generate_docx_file($post->ID, $fileName);
            }
        }

        function generate_docx_file($id, $fileName)
        {
            $post = get_post($id);

            if (!$this->options[$post->post_type]) {
                return false;
            }

            // to avoid duplicate function error
            if(!class_exists('CreateDocx')) {
                require_once(WPPT0DOCX_PATH . '/phpdocx/classes/CreateDocx.php');
            }

            // create a new DOCX
            $docx = new CreateDocx();
            $docx->embedHTML($post->post_content);
            $docx->createDocx(WP_CONTENT_DIR . '/uploads/' . $fileName);

            $filePath = WP_CONTENT_DIR . '/uploads/' . $fileName . '.docx';

            if (!is_readable($filePath)) {
                return false;
            }

            if (ini_get('zlib.output_compression')) {
                ini_set('zlib.output_compression', 'Off');
            }

            header('Content-Type: application/force-download');
            header('Content-Disposition: attachment; filename="' . rawurldecode($fileName . '.docx') . '"');
            header("Content-Transfer-Encoding: binary");
            header("Cache-control: private");
            header('Pragma: private');
            header("Expires: Mon, 1 Jan 1900 00:00:00 GMT");

            header("Content-Length: " . filesize($filePath));
            readfile($filePath);

            return true;
        }

        function add_button($content)
        {
            $button = $this->display_icon();
            $buttonContent = $button . $content;
            return $buttonContent;
        }

        function display_icon()
        {
            // return nothing if no permission
            if ($this->options['nonPublic'] and !is_user_logged_in()) {
                return;
            }

            if (!(is_single() or is_page())) {
                return;
            }

            // remove icon from docx file
            if($_GET['format'] == 'docx') {
                return;
            }

            global $post;

            if (!$this->options[$post->post_type]) {
                return false;
            }

            // Change querystring separator for those who do not have pretty URL enabled
            $qst = get_permalink($post->ID);
            $qst = parse_url($qst);
            if ($qst['query']) {
                $qst = '&format=docx';
            } else {
                $qst = '?format=docx';
            }

            return '<a class="posttodocx" target="_blank" rel="noindex,nofollow" href="' . get_permalink($post->ID) . $qst . '" title="Download DOCX"><img alt="Download DOCX" src="' . WPPT0DOCX_URL . '/assets/images/docx.png"></a>';
        }

        function on_activate()
        {
            // set default options on activate
            $default = array(
                'post' => 1,
                'page' => 1,
                );
            if (!get_option('posttodocx')) {
                add_option('posttodocx', $default);
            }

            // create directory and move logo to upload directory
            if (!is_dir(WP_CONTENT_DIR . '/uploads')) {
                mkdir(WP_CONTENT_DIR . '/uploads');
            }
        }

    }

    $posttodocx = new Posttodocx();

    if(!function_exists('posttodocx_display_icon')){
        function posttodocx_display_icon()
        {
            global $posttodocx;

            return $posttodocx->display_icon();
        }
    }
}
