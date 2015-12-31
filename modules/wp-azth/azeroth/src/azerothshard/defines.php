<?php

$azthRoleRel = array(
    // staff
    "administrator" => array("tbg" => 3, array("role" => 100003, "lvl" => 3, "realm" => -1)), // administrators cannot be grouped in simple-press
    "azeroth_staff" => array("sp" => 4, "tbg" => 6),
    "azeroth_staff_trial" => array("sp" => 6, "tbg" => 21),
    "azth_staff_council" => array("sp" => 33, "tbg" => 1),
    "azth_staff_legal_officer" => array("sp" => 9, "tbg" => 25),
    "azth_staff_finacial_officer" => array("sp" => 10, "tbg" => 5),
    "azth_staff_marketing" => array("sp" => 11, "tbg" => 20),
    "azth_staff_gm_tier_2" => array("sp" => 12, "tbg" => 18, "game" => array("role" => 100002, "lvl" => 2, "role_realm" => -1, "lvl_realm" => 1)),
    "azth_staff_gm_tier_1" => array("sp" => 13, "tbg" => 17, "game" => array("role" => 100001, "lvl" => 1, "role_realm" => -1, "lvl_realm" => 1)),
    "azth_staff_master_story_teller" => array("sp" => 14, "tbg" => 8, "game" => array("role" => 100006, "lvl" => 2, "role_realm" => -1, "lvl_realm" => 1)),
    "azth_staff_story_teller" => array("sp" => 5, "tbg" => 13, "game" => array("role" => 100010, "lvl" => 1, "role_realm" => -1, "lvl_realm" => 1)),
    "azth_staff_master_entertainer" => array("sp" => 16, "tbg" => 33, "game" => array("role" => 100004, "lvl" => 2, "role_realm" => -1, "lvl_realm" => 1)),
    "azth_staff_entertainer" => array("sp" => 17, "tbg" => 32, "game" => array("role" => 100005, "lvl" => 1, "role_realm" => -1, "lvl_realm" => 1)),
    "azth_staff_master_game_tester" => array("sp" => 18, "tbg" => 4, "game" => array("role" => 100014, "lvl" => 2, "role_realm" => 2, "lvl_realm" => 2)),
    "azth_staff_game_tester" => array("sp" => 19, "tbg" => 9, "game" => array("role" => 100013, "lvl" => 1, "role_realm" => 2, "lvl_realm" => 2)),
    "azth_staff_master_developer" => array("sp" => 20, "tbg" => 10),
    "azth_staff_game_developer" => array("sp" => 21, "tbg" => 2),
    "azth_staff_web_developer" => array("sp" => 22, "tbg" => 15),
    "azth_staff_web_designer" => array("sp" => 23, "tbg" => 16),
    "azth_staff_creative_manager" => array("sp" => 24, "tbg" => 26),
    "azth_staff_graphic_designer" => array("sp" => 25, "tbg" => 19),
    "azth_staff_communication_manager" => array("sp" => 26, "tbg" => 27),
    "azth_staff_promoter" => array("sp" => 27, "tbg" => 28),
    "azth_staff_web_master" => array("sp" => 28, "tbg" => 29),
    "azth_staff_librarian" => array("sp" => 29, "tbg" => 31),
    "azth_staff_editor" => array("sp" => 7, "tbg" => 32),
    "azth_staff_web_moderator" => array("sp" => 3, "tbg" => 12),
    "azth_staff_web_supporter" => array("sp" => 32, "tbg" => 11),
    // players
    "azth_player_gdr" => array("sp" => 8)
);

define('COUPON_I80', 'level80');
define('PRODUCTID_I80', 1532);
