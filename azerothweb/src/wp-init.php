<?php

namespace Azth;

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */

define('AZTH_PATH_WP', AZTH_PATH_MODULES . DS . 'wp' . DS);

require_once AZTH_IS_CLI ? "wp-init-cli.php" : "wp-init-html.php";
