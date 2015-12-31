<?php

function user_meta_updated($meta_id, $object_id, $meta_key, $_meta_value) {
    if (!$user = get_userdata($object_id))
        return false;

    $username = $user->user_login;

    switch ($meta_key) {
        // account activation
        case "active":
            if ($_meta_value == 1) {
                $result = unbanTcAccount($username);
            }
            break;
        // expansion
        case "pie_dropdown_3":
            setTcAccountAddon($username, $_meta_value[0]);
            break;
    }
}

add_action('update_user_meta', __NAMESPACE__ . '\user_meta_updated', 10, 4);


function user_meta_updated($meta_id, $object_id, $meta_key, $_meta_value) {
    if (!$user = get_userdata($object_id))
        return false;

    $username = $user->user_login;

    switch ($meta_key) {
        // account activation
        case "active":
            if ($_meta_value == 1) {
                $result = unbanTcAccount($username);
            }
            break;
    }
}



add_action('update_user_meta', __NAMESPACE__ . '\user_meta_updated', 10, 4);
