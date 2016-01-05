<?php

namespace Azth;

if (isset($_GET["azth-cmd"]) && $_GET["azth-cmd"] == "run-test") {
    require_once AZTH_PATH_WP . 'wp-load.php';

    require_once 'tests/html.php';
} else {
    define('WP_USE_THEMES', true);

    /** Loads the WordPress Environment and Template */
    require_once AZTH_PATH_WP . 'wp-blog-header.php';
}
