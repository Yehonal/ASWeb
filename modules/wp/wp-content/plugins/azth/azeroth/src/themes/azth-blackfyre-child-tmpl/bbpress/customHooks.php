<?php

// define the bbp_theme_after_topic_title callback
function action_bbp_theme_after_topic_title() {
    ?>
    <a class="bbp-topic-permalink" href="<?php bbp_topic_permalink(); ?>"><?php echo " - <small>" . substr(strip_tags(bbp_get_topic_content()), 0, 40) . "... </small>"; ?></a>
    <?php
}

;

// add the action
add_action('bbp_theme_after_topic_title', 'action_bbp_theme_after_topic_title', 10, 0);


function azth_init_tinymce() {
    ?>
    <!-- http://tinymce.cachefly.net/4.2/tinymce.min.js  | <?= get_stylesheet_directory_uri() ?>/tinymce/tinymce.min.js -->
    <script src="http://tinymce.cachefly.net/4.2/tinymce.min.js"></script>
    <script type="text/javascript">
        tinymce.init({
            selector: "#postitem",
            setup: function (editor) {
                editor.on('change', function () {
                    editor.save();
                });
            },
            skin: "tundora",
            skin_url: "<?= get_stylesheet_directory_uri() ?>/tinymce/skins/tundora",
            content_css: "<?php
    echo get_template_directory_uri() . '/style.css,';
    echo get_template_directory_uri() . '/css/blackoot.min.css,';
    echo get_stylesheet_directory_uri() . '/style.css,';
    echo get_stylesheet_directory_uri() . '/css/blacksnake24.css,';
    echo get_stylesheet_directory_uri() . '/css/bbpress.css,';
    echo get_stylesheet_directory_uri() . '/css/tinymce-front.css';
    ?>"
        });
    </script>
    <!--<link rel="stylesheet" id="editor-buttons-css" href="<?= get_stylesheet_directory_uri() ?>/tinymce/skins/tundora/skin.min.css" type="text/css" media="all">-->
    <?php
}

//add_action('wp_footer', 'azth_init_tinymce');

function azth_bbp_enable_visual_editor($args = array()) {
    return true;
}

add_filter('bbp_use_wp_editor', 'azth_bbp_enable_visual_editor');


/**
 * Allow upload media in bbPress
 *
 * This function is attached to the 'bbp_after_get_the_content_parse_args' filter hook.
 */
function azth_bbpress_upload_media($args) {
    $args['media_buttons'] = true;
    $args['tinymce'] = true;
    $args['teeny'] = false;

    return $args;
}

add_filter('bbp_after_get_the_content_parse_args', 'azth_bbpress_upload_media');

function azth_format_tinymce($in) {
    $in['content_css'] = get_template_directory_uri() . '/style.css,'
            . get_stylesheet_directory_uri() . '/style.css,'
            . get_stylesheet_directory_uri() . '/css/tinymce-front.css';

    return $in;
}

add_filter('tiny_mce_before_init', 'azth_format_tinymce');

function azth_editor_settings($settings) {
    if (!is_admin()) { // not in admin panel
        $settings["editor_css"] = "<link rel='stylesheet' href='" . get_stylesheet_directory_uri() . "/css/tinymce-front-editor.css'>";
    }

    return $settings;
}

//add_filter('wp_editor_settings', 'azth_editor_settings');




