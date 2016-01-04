<?php

function azthForumRedirect() {
    header('Location: /forums/profile/' . bp_displayed_user_id() . '/');
}

function azthPoints() {
    ?>
    <h1>TEST</h1>
    <?php
}

function azthBpTabs() {
    global $bp;
    bp_core_new_nav_item(array(
        'name' => __('Forum Profile'),
        'slug' => 'forum_profile',
        'screen_function' => 'azthForumRedirect',
        'position' => 11)
    );

    /* bp_core_new_nav_item(array(
      'name' => __('Azeroth Points'),
      'screen_function' => 'azthPoints',
      'position' => 30)
      ); */
}

add_action('bp_setup_nav', 'azthBpTabs');

$azthBpGroupRoleRel = array(
    3 => "azth_player_gdr" // GDR
);

function azthBpLeaveGroup($group_id, $user_id) {
    global $azthBpGroupRoleRel;

    if (!isset($azthBpGroupRoleRel[$group_id]))
        return;

    if ($user = get_user_by('id', $user_id))
        $user->remove_role($azthBpGroupRoleRel[$group_id]);
}

add_action('groups_leave_group', 'azthBpLeaveGroup', 10, 2);
add_action('groups_remove_member', 'azthBpLeaveGroup', 10, 2);

/**
 * Inverted parameters
 * @param type $user_id
 * @param type $group_id
 */
function azthBpMembershipAdd($user_id, $group_id) {
    azthBpJoinGroup($group_id, $user_id);
}

function azthBpJoinGroup($group_id, $user_id) {
    global $azthBpGroupRoleRel;

    if (!isset($azthBpGroupRoleRel[$group_id]))
        return;

    if ($user = get_user_by('id', $user_id))
        $user->add_role($azthBpGroupRoleRel[$group_id]);
}

add_action('groups_join_group', 'azthBpJoinGroup', 10, 2);
add_action('groups_accept_invite', 'azthBpJoinGroup', 10, 2);
add_action('groups_membership_accepted', 'azthBpMembershipAdd', 10, 2);
