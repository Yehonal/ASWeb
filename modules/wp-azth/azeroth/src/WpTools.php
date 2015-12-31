<?php

namespace Azth;

class WpTools {

    /**
     * 
     * @global \BeemAg\WP_User $current_user
     * @param Integer $id : if no id is passed, then it get the $current_user
     * @return String
     */
    public static function getUserFirstRole($id = NULL) {
        if (!$id) {
            global $current_user;
        } else {
            $current_user = new \WP_User($id);
        }

        $user_roles = $current_user->roles;

        $user_role = array_shift($user_roles);

        return $user_role;
    }

    public static function hasRole($role, $id = NULL) {
        if (!$id) {
            global $current_user;
        } else {
            $current_user = new \WP_User($id);
        }

        if (!empty($current_user->roles) && is_array($current_user->roles)) {
            return in_array($role, $current_user->roles);
        }
    }

    public static function getRoleDisplayName($role) {
        global $wp_roles;

        return $wp_roles->roles[$role]['name'];
    }

    public static function userIdExists($user) {

        global $wpdb;

        $count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->users WHERE ID = '$user'");

        return $count == 1;
    }

    public static function getCurrentDate() {
        $today = getdate();
        $month = $today['month'];
        $mday = $today['mday'];
        $year = $today['year'];
        return "$mday/$month/$year";
    }

    public static function getDataVal($array, $key) {
        if (isset($array[$key]) && isset($array[$key][0])) {
            return $array[$key][0]; // get the first val
        }
    }

    public static function sanitizeFileName($filename) {
        $filename_raw = $filename;
        $special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}");
        $special_chars = apply_filters('sanitize_file_name_chars', $special_chars, $filename_raw);
        $filename = str_replace($special_chars, '', $filename);
        $filename = preg_replace('/[\s-]+/', '-', $filename);
        $filename = trim($filename, '.-_');
        return apply_filters('sanitize_file_name', $filename, $filename_raw);
    }
    
}