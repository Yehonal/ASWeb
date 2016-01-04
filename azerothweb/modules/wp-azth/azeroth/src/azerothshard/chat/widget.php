<?php

namespace Azth;

add_shortcode('azth-chat', __NAMESPACE__ . '\renderChat');

function renderChat() {
    ob_start();

    $current_user = wp_get_current_user();
    $nickname = $current_user->nickname;
    ?>
    <div class="fluidMedia">
        <iframe src="http://widget.mibbit.com/?settings=fe7334f1c0db6efe8e73f8fac9e68e29&server=rizon.mibbit.org%3A%2B6697&channel=%23azerothshard&nick=<?= $nickname ?>&autoConnect=true" height="570" frameborder="0"> </iframe>
    </div>
    <?php
    return ob_get_clean();
}
