<?php

namespace Azth;

defined("DS") or define('DS', DIRECTORY_SEPARATOR);

define('AZTH_PATH_WPLG', realpath(dirname(__FILE__) . DS . '..' . DS) . DS);
define('AZTH_PATH_WPLG_CONF', AZTH_PATH_WPLG . 'conf' . DS);
define('AZTH_PATH_WPLG_SRC', AZTH_PATH_WPLG . DS . 'src' . DS );
define('AZTH_PATH_WPLG_JS', AZTH_PATH_WPLG_SRC . 'js' . DS);
define('AZTH_PATH_WPLG_CSS', AZTH_PATH_WPLG_SRC . 'css' . DS);

define('AZTH_URI_WPLG', '/modules/wp-azth/azeroth/src/');
define('AZTH_URI_WPLG_JS', AZTH_URI_WPLG . 'js/');
define('AZTH_URI_WPLG_CSS', AZTH_URI_WPLG . 'css/');


register_theme_directory(AZTH_PATH_WPLG_SRC . 'themes');

// SITE IDs
define("AZEROTHSHARD", 3);

defined("IS_LOCAL") OR define('IS_LOCAL', !(
                in_array($_SERVER['HTTP_HOST'], array('localhost', '127.0.0.1')) === false &&
                $_SERVER['REMOTE_ADDR'] !== '127.0.0.1' &&
                $_SERVER['REMOTE_ADDR'] !== '::1'
        ));

function disableAdminPanel() {
    if (is_admin() && (
            WpTools::hasRole("subscriber")
            //|| current_user_can( 'another' ) 
            ) && !( defined('DOING_AJAX') && DOING_AJAX )) {
        die("Non puoi accedere! torna da dove sei venuto!");
        exit;
    }
}

add_action('init', __NAMESPACE__ . '\disableAdminPanel');

function appendMessage($message, $type = '') {
    // Success is the default
    if (empty($type)) {
        $type = 'success';
    }

    $newMsg = $_COOKIE['hw2-message'] . " " . $message;

    @setcookie('hw2-message', $newMsg, time() + 60 * 60 * 24, COOKIEPATH);
    @setcookie('hw2-message-type', $type, time() + 60 * 60 * 24, COOKIEPATH);
}

function bpCustomMessageSetup() {
    // Get BuddyPress
    $bp = buddypress();

    if (isset($_COOKIE['hw2-message'])) {
        $bp->template_message .= stripslashes($_COOKIE['hw2-message']);
    }

    if (isset($_COOKIE['hw2-message-type'])) {
        $bp->template_message_type .= stripslashes($_COOKIE['hw2-message-type']);
    }

    if (isset($_COOKIE['hw2-message'])) {
        @setcookie('hw2-message', false, time() - 1000, COOKIEPATH);
    }

    if (isset($_COOKIE['hw2-message-type'])) {
        @setcookie('hw2-message-type', false, time() - 1000, COOKIEPATH);
    }
}

add_action('bp_actions', __NAMESPACE__ . '\bpCustomMessageSetup', 10);

function remove_wp_logo($wp_admin_bar) {
    $wp_admin_bar->remove_node('wp-logo');
}

add_action('admin_bar_menu', __NAMESPACE__ . '\remove_wp_logo', 999);

/**
 * BBPRESS
 */
function forum_icons() {
    if ('forum' == get_post_type()) {
        global $post;
        if (has_post_thumbnail($post->ID))
            echo get_the_post_thumbnail($post->ID, 'thumbnail', array('class' => 'alignleft forum-icon'));
    }
}

add_post_type_support('forum', array('thumbnail'));
add_action('bbp_theme_after_forum_title', __NAMESPACE__ . '\forum_icons');

require_once AZTH_PATH_WPLG_SRC . "checkNames.php";
require_once AZTH_PATH_WPLG_SRC . "PageTemplater.php";
require_once AZTH_PATH_WPLG_SRC . "WpTools.php";

$blogId = get_current_blog_id();

switch ($blogId) {
    case AZEROTHSHARD:
        require_once AZTH_PATH_WPLG_SRC . "/azerothshard/main.php";
        break;
}
