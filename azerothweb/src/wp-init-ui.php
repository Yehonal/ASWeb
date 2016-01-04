<?php

namespace Azth;

if ($_GET["azth_cmd"]=="run-test") {
    require_once AZTH_PATH_WP . 'wp-load.php';
    
    require_once 'tests/ui.php';
} else {
    /** Loads the WordPress Environment and Template */
    require_once AZTH_PATH_WP . 'wp-blog-header.php';
}
