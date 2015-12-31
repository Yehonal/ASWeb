<?php

namespace Azth;

// Make nickname & display_name unique
// and automatically change non unique nicks & display name to username
// in case you already have existing users
// by Ashok & Vaughan Montgomery
/*
 * adding action when user profile is updated
 */
add_action('personal_options_update', __NAMESPACE__ . '\check_display_name');
add_action('edit_user_profile_update', __NAMESPACE__ . '\check_display_name');

function check_display_name($user_id) {
    // Getting user data and user meta data
    $err = findNameDuplicates($_POST['display_name'], $_POST['nickname'], $_POST['user_id']);

    foreach ($err as $key => $e) {
        // If display name or nickname already exists
        if ($e >= 1) {
            $err[$key] = $_POST['username'];
            // Adding filter to corresponding error
            add_filter('user_profile_update_errors', __NAMESPACE__ . "\check_{$key}_field", 10, 3);
        }
    }
}

function findNameDuplicates($displayName, $nickname, $userId = null) {
    /* @var $wpdb \wpdb */
    global $wpdb;

    $displaySearch = "SELECT COUNT(ID) FROM $wpdb->users WHERE display_name = %s ";
    $nickSearch = "SELECT COUNT(ID) FROM $wpdb->users as users, $wpdb->usermeta as meta WHERE users.ID = meta.user_id AND meta.meta_key = 'nickname' AND meta.meta_value = %s";


    // Getting user data and user meta data
    if ($userId) {
        $err['display'] = intval($wpdb->get_var($wpdb->prepare($displaySearch . " AND ID <> %d", $displayName, $userId)));
        $err['nick'] = intval($wpdb->get_var($wpdb->prepare($nickSearch . " AND users.ID <> %d", $nickname, $userId)));
    } else {
        $err['display'] = intval($wpdb->get_var($wpdb->prepare($displaySearch, $displayName)));
        $err['nick'] = intval($wpdb->get_var($wpdb->prepare($nickSearch, $nickname)));
    }

    return $err;
}

/*
 * Filter function for display name error
 */

function check_display_field($errors, $update, $user) {
    $errors->add('display_name_error', __('<span id="IL_AD9" class="IL_AD">Sorry</span>, Display Name is already in use. It needs to be unique.'));
    return false;
}

/*
 * Filter function for nickname error
 */

function check_nick_field($errors, $update, $user) {
    $errors->add('display_nick_error', __('Sorry, Nickname is already in use. It needs to be unique.'));
    return false;
}

/*
 * Check for duplicate display name and nickname and replace with username
 * This function is not called but can be runned eventually to fix
 * duplicates
 */

function display_name_and_nickname_duplicate_check() {
    global $wpdb;
    $query = $wpdb->get_results("SELECT * FROM $wpdb->users");
    $query2 = $wpdb->get_results("SELECT * FROM $wpdb->users as users, $wpdb->usermeta as meta WHERE users.ID = meta.user_id AND meta.meta_key = 'nickname'");
    $c = count($query);
    for ($i = 0; $i < $c; $i++) {
        for ($j = $i + 1; $j < $c; $j++) {
            if ($query[$i]->display_name == $query[$j]->display_name) {
                wp_update_user(
                        array(
                            'ID' => $query[$i]->ID,
                            'display_name' => $query[$i]->user_login
                        )
                );
            }
            if ($query2[$i]->meta_value == $query2[$j]->meta_value) {
                update_user_meta($query2[$i]->ID, 'nickname', $query2[$i]->user_login, $prev_value);
            }
        }
    }
}
