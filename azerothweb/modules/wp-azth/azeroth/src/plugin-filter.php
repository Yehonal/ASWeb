<?php

namespace Azth;

add_filter('option_active_plugins', AZTH_NS . 'option_active_plugins');

function option_active_plugins($plugins) {
    if (AZTH_IS_CLI) {
        $cli_plugins = array(
        );

        foreach ($plugins as $plugin) {
            $key = array_search($plugin, $cli_plugins);
            if (false === $key) {
                unset($plugins[$key]);
            }
        }
    }

    return $plugins;
}
