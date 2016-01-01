<?php
include "bbpress/customHooks.php";

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
});

add_action('after_setup_theme', function() {
    remove_filter('get_avatar', 'blackfyre_be_gravatar_filter');
    remove_filter('bp_core_fetch_avatar', 'filter_bp_core_fetch_avatar');
    remove_filter('bp_core_mysteryman_src', 'blackfyre_be_gravatar_filter_admin');
    remove_filter('pre_option_default_role', 'blackfyre_defaultrole');
}, 999);

add_filter('avatar_defaults', function ($avatar_defaults) {
    $myavatar = get_template_directory_uri() . '/img/defaults/default-profile.jpg';
    $avatar_defaults[$myavatar] = "Blackfyre Avatar";
    return $avatar_defaults;
});

add_action('wp_head', 'azth_hook_head');

function azth_hook_head() {
    ?>
    <link rel="stylesheet" media="screen" href="<?= get_stylesheet_directory_uri() ?>/xmas/lights/christmaslights.css" />
    <script src='<?= get_stylesheet_directory_uri() ?>/xmas/lights/soundmanager2-nodebug-jsmin.js'></script>
    <script src='http://yui.yahooapis.com/combo?2.6.0/build/yahoo-dom-event/yahoo-dom-event.js&amp;2.6.0/build/animation/animation-min.js'></script>
    <script src='<?= get_stylesheet_directory_uri() ?>/xmas/lights/christmaslights.js'></script>
    <script src='<?= get_stylesheet_directory_uri() ?>/xmas/snowstorm-min.js'></script>
    <script type="text/javascript">
        snowStorm.zIndex = 9999;
        var urlBase = '<?= get_stylesheet_directory_uri() ?>/xmas/lights/';
        soundManager.url = '<?= get_stylesheet_directory_uri() ?>/xmas/lights/';
    </script>
    <?php
    
    if (is_admin_bar_showing()) {
        ?>
    <style>
        #lights {
            top:31px;
        }
    </style>
        <?php
    }
}
