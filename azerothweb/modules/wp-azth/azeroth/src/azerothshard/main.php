<?php

namespace Azth;

define('AZTH_PATH_WPLG_AS', AZTH_PATH_WPLG_SRC . 'azerothshard' . DS);

define('AZTH_URI_WPLG_AS', AZTH_PATH_WPLG_SRC . 'azerothshard/');

define("EXPANSION_WOTLK", 2);
define("EXPANSION_TBC", 1);
define("EXPANSION_CLASSIC", 0);

require_once "defines.php";
require_once AZTH_PATH_WPLG_CONF . "conf.php";

//include_once ABSPATH . "/wp-content/plugins/buddypress/bp-core/bp-core-functions.php";
require_once AZTH_PATH_WPLG_AS . "ServerReg.php";
require_once AZTH_PATH_WPLG_AS . "chat/widget.php";
require_once AZTH_PATH_WPLG_AS . "shoutbox/shoutbox.php";
require_once AZTH_PATH_WPLG_AS . "woocommerce.php";
require_once AZTH_PATH_WPLG_AS . "mycred.php";
require_once AZTH_PATH_WPLG_AS . "buddypress/functions.php";

add_shortcode('azth-shoutbox', __NAMESPACE__ . '\showShoutbox');

/**
 * Even if the server account has been registered correctly, 
 * Maybe we should assure that wordpress user has been registered too, otherwise
 * we have to delete just created game account
 */
function validateUserSignup() {
    $bp = \buddypress();

    $errors = &$bp->signup->errors;

    // if there are errors yet
    // then we do not create the game account
    if (!empty($errors))
        return;

    $nickname = $_POST['field_1'];

    // Getting user data and user meta data
    $err = \Azth\findNameDuplicates($nickname, $nickname);

    foreach ($err as $key => $e) {
        // If display name or nickname already exists
        if ($e >= 1) {
            /*
             * We need to use $bp since this field is related to buddypress
             * and not considered if added to $errors
             */
            $errors['field_1'] = "Questo nickname già esiste";
            return;
        }
    }

    $username = $bp->signup->username;

    if (!$username || \username_exists($username)) {
        $errors['signup_username'] = "Questo nome utente è già esistente";
        return;
    }

    if ($username !== strtolower($username)) {
        $errors['signup_username'] = "Il nome utente deve contenere tutti caratteri minuscoli";
        return;
    }

    $password = $_POST["signup_password"];

    if (strlen($password) > 16) {
        $errors['signup_password'] = "La password non deve superare i 16 caratteri";
        return;
    }

    $expansions = array(
        "Wrath of The Lich King" => EXPANSION_WOTLK,
        "The Burning Crusade" => EXPANSION_TBC,
        "Classic" => EXPANSION_CLASSIC
    );

    $email = $bp->signup->email;
    $expansion = $_POST['field_2'];

    $reference = $_POST['field_9'];

    $addon = array_key_exists($expansion, $expansions) ? $expansions[$expansion] : 2;

    $result = createTcAccountFull($username, $password, $email, $addon, true);

    if ($result instanceof \Exception) {
        // print message using buddypress method
        //bp_core_add_message($result->getMessage(), 'error');
        $errors['signup_username'] = "Game Server error: " . $result->getMessage();
    }

    $bp->signup->azthJustCreated = true;

    return;
}

// we should keep with low priority because we need to do default and captcha check before
add_action('bp_signup_validate', __NAMESPACE__ . '\validateUserSignup', 999);

function userAfterBpCreated() {
    $bp = \buddypress();

    if ($bp->signup->azthJustCreated && $bp->signup->step != 'completed-confirmation') {
        echo "HO CANCELLATO L'ACCOUNT DAL SERVER";
        deleteTcAccount($bp->signup->username);
    }
}

add_action('bp_complete_signup', __NAMESPACE__ . '\validateUserSignup');

function user_before_create($user_login) {
    if ($result = unbanTcAccount($user_login) instanceof \Exception)
        die("Game server error: " . $result->getMessage());

    return $user_login;
}

add_filter('pre_user_login', __NAMESPACE__ . '\user_before_create');

/**
 * 
 * @param \BP_XProfile_ProfileData $data
 */
function user_xprofile_before_update($data) {
    if (!$user = \get_userdata($data->user_id))
        return false;

    $username = $user->user_login;

    switch ($data->field_id) {
        case 1:
            //NICKNAME
            // Getting user data and user meta data
            $err = \Azth\findNameDuplicates($data->value, $data->value);

            foreach ($err as $key => $e) {
                // If display name or nickname already exists
                if ($e >= 1) {
                    $data->field_id = 0;
                    \Azth\appendMessage("[ Nickname already exits ]", 'error');
                }
            }
            break;
        case 2:

            // EXPANSION
            $expansions = array(
                "Wrath of The Lich King" => EXPANSION_WOTLK,
                "The Burning Crusade" => EXPANSION_TBC,
                "Classic" => EXPANSION_CLASSIC
            );

            $addon = array_key_exists($data->value, $expansions) ? $expansions[$data->value] : 2;
            if ($result = setTcAccountAddon($username, $addon) instanceof \Exception) {
                $data->field_id = 0;
                \Azth\appendMessage("[ Game server error: " . $result->getMessage() . " ]", 'error');
            }
            break;
    }
}

add_action("xprofile_data_before_save", __NAMESPACE__ . '\user_xprofile_before_update', 10, 1);

function user_meta_added($user_id, $meta_key, $meta_value) {
    if (!$user = get_userdata($user_id))
        return false;

    $username = $user->user_login;
    $email = $user->user_email;

    switch ($meta_key) {
        // account activation
        case "wp_" . AZEROTHSHARD . "_capabilities":
            if (!\is_user_member_of_blog($user_id, AZEROTHSHARD)) {
                //Exist's but is not user to the current blog id
                //$result = add_user_to_blog( $blog_id, $user_id, $_POST['user_role']);
                if (array_key_exists("pwd", $_POST) && $_POST["pwd"]) {
                    $password = $_POST["pwd"];
                    //[TODO] We should add a check: if the server is not reachable we should delete
                    // the just created metadata and show an error
                    /* @var $result \Exception */
                    if ($result = createTcAccountFull($username, $password, $email, EXPANSION_WOTLK, true) instanceof \Exception) {
                        die("Game server error: " . $result->getMessage());
                    }
                }
            }

            break;
    }
}

/**
 * This action is created by do_action( "add_{$meta_type}_meta"
 * in add_metadata method just before inserting metadata
 */
add_action('add_user_meta', __NAMESPACE__ . '\user_meta_added', 10, 3);

/**
 * We cannot make it global since users from other sites could not have
 * an azerothshard account
 * @param type $user_id
 * @param \WP_User $old_user_data
 */
function user_profile_update($user_id, $old_user_data) {
    $user = get_userdata($user_id)->data;

    // Update user email
    if ($user->user_email != $old_user_data->user_email) {
        /* @var $result \Exception */
        $result = setTcAccountEmail($user->user_login, $user->user_email);
        if ($result instanceof \Exception)
            die("Game server error: " . $result->getMessage());
    }

    if (isset($_POST['pass1']) && $_POST['pass1'] != '') {
        /* @var $result \Exception */
        $result = setTcAccountPassword($user->user_login, $_POST['pass1']);
        if ($result instanceof \Exception)
            die("Game server error: " . $result->getMessage());
    }
}

add_action('profile_update', __NAMESPACE__ . '\user_profile_update', 10, 2);

/**
 * 
 * @param \WP_User $user
 * @param String $new_pass
 */
function user_password_reset($user, $new_pass) {
    if ($result = setTcAccountPassword($user->user_login, $new_pass) instanceof \Exception)
        die("Game server error: " . $result->getMessage());
}

add_action('password_reset', __NAMESPACE__ . '\user_password_reset', 10, 2);

//\Azth\PageTemplater::getInstance()->addTemplate(AZTH_PATH_WPLG_AS . "bbpress_tmpl.php", "BBPres Forum");


function after_delete($user_id) {
    global $wpdb;

    $user_obj = get_userdata($user_id);
    $email = $user_obj->user_email;
    $username = $user_obj->user_login;

    deleteTcAccount($username);

    // clean buddypress data on user delete, even if there are other users garbage data
    $wpdb->query("DELETE FROM `wp_bp_xprofile_data` WHERE `user_id` NOT IN ( SELECT ID FROM wp_users );");
}

add_action('wpmu_delete_user', __NAMESPACE__ . '\after_delete', 10, 1);

function getHead() {
    ob_start();
    ?>	
    <link rel="stylesheet" href="<?php echo AZTH_URI_WPLG_AS ?>css/style.css">
    <?php
    echo ob_get_clean();
}

add_action('wp_head', __NAMESPACE__ . '\getHead');

function getFooter() {
    ob_start();
    ?>	
    <script src="http://tinymce.cachefly.net/4.2/tinymce.min.js"></script>
    <script>
        tinymce.init({selector: '#postitem',
            /*skin: "lightgray",
             theme: "modern",
             width: 300,
             height: 300,*/
            plugins: [
                "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                "save table contextmenu directionality emoticons template paste textcolor"
            ],
            body_class: "content locale-it-it",
            content_css: "http://www.azerothshard.ga/wp-includes/css/dashicons.min.css?ver=4.3,http://www.azerothshard.ga/wp-includes/js/tinymce/skins/wordpress/wp-content.css?ver=4.3,http://www.azerothshard.ga/wp-content/themes/blackoot-lite/css/editor-style.css",
            toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons",
            style_formats: [
                {title: 'Bold text', inline: 'b'},
                {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
                {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
                {title: 'Title 1', block: 'h1'},
                {title: 'Title 2', block: 'h2'},
                {title: 'Title 3', block: 'h3'},
                {title: 'Table styles'},
                {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
            ]
        });
    </script>
    <?php
    echo ob_get_clean();
}

//add_action('wp_footer', __NAMESPACE__ . '\getFooter', 200);

/**
 * HANDLE ROLES
 */
function azthAssignUserRole($userid, $role) {
    global $azthRoleRel, $wpdb;



    if ($user = get_user_by('id', $userid)) {
        $customWpdb = new \wpdb(AZTH_DB_USER, AZTH_DB_PASS, AZTH_DB_AUTH, AZTH_DB_HOST);
        $scope = 1;

        $gameAccId = $customWpdb->get_var("SELECT id FROM account WHERE username = '" . $user->user_login . "'");

        // starts from 1 because at this point the "first" role has been already assigned
        if (count($user->roles) <= 1) {
            // remove from buggenie
            $delSql = "DELETE FROM tbg_teammembers WHERE scope = 1 AND uid = (SELECT id FROM tbg_users WHERE username = '" . $user->user_login . "')";

            $wpdb->query($delSql);

            // remove from simplepress
            foreach ($azthRoleRel as $wp => $rel) {
                sp_remove_membership($rel["sp"], $userid);
            }

            // remove from game



            $customWpdb->query("DELETE FROM rbac_account_permissions WHERE accountId=" . $gameAccId
                    . " AND permissionId >= 100000");

            $customWpdb->query("DELETE FROM account_access WHERE id=" . $gameAccId);
        }

        if (isset($azthRoleRel[$role])) {
            // auto assign mapped roles to buggenie
            if (isset($azthRoleRel[$role]["tbg"])) {
                $tid = $azthRoleRel[$role]["tbg"];

                $uid = $wpdb->get_var($wpdb->prepare("SELECT id FROM tbg_users WHERE username = %s", $user->user_login
                ));

                if ($uid) {
                    $id = $wpdb->get_var($wpdb->prepare("SELECT id FROM tbg_teammembers WHERE scope=@SCOPE AND uid = %d AND tid = %d", $uid, $tid
                    ));

                    if (!$id) {
                        $insSql = $wpdb->prepare("INSERT INTO tbg_teammembers (`scope`,`uid`,`tid`) VALUES (%d,%d,%d);", $scope, $uid, $tid);

                        $wpdb->query($insSql);
                    }
                } else {
                    echo "This account doesn't have a Buggenie profile";
                }
            }

            // auto assign mapped roles to simple-press
            if (isset($azthRoleRel[$role]["sp"]))
                sp_add_membership($azthRoleRel[$role]["sp"], $userid);

            // auto assign mapped role for server game
            if (isset($azthRoleRel[$role]["game"])) {
                $customWpdb->query("REPLACE INTO account_access (`id`,`gmlevel`,`RealmID`) VALUES($gameAccId," . $azthRoleRel[$role]["game"]["lvl"] . "," . $azthRoleRel[$role]["game"]["lvl_realm"] . ")");
                $customWpdb->query("REPLACE INTO rbac_account_permissions (`accountId`,`permissionId`,`granted`,`realmId`) VALUES($gameAccId," . $azthRoleRel[$role]["game"]["role"] . ",1," . $azthRoleRel[$role]["game"]["role_realm"] . ")");
                //executeSoapCommand('account set gmlevel ' . $user->user_login . ' ' . $azthRoleRel[$role]["game"]["lvl"] .' '. $azthRoleRel[$role]["game"]["lvl_realm"]);
                //executeSoapCommand('rbac account grant ' . $user->user_login . ' ' . $azthRoleRel[$role]["game"]["role"] .' '. $azthRoleRel[$role]["game"]["role_realm"]);
                executeSoapCommand('reload rbac');
            }
        }
    }
}

add_action('add_user_role', __NAMESPACE__ . '\azthAssignUserRole', 10, 2);

function azthRemoveUserRole($userid, $role) {
    global $azthRoleRel;






    // auto remove mapped roles to simple-press 
    //if (isset($azthRoleRel[$role]))
    //    sp_remove_membership($azthRoleRel[$role]["sp"], $userid);
}

add_action('remove_user_role', __NAMESPACE__ . '\azthRemoveUserRole', 10, 2);

function azthSetUserRole($userid, $role, $old_roles) {
    azthRemoveUserRole($userid, $old_roles);
    azthAssignUserRole($userid, $role);
}

add_action('set_user_role', __NAMESPACE__ . '\azthSetUserRole', 10, 3);


/**
 * OTHER
 */
/*
  remove node from admin bar
 */
add_action('admin_bar_menu', __NAMESPACE__ . '\azth_admin_bar_menu', 999);

function azth_admin_bar_menu($wp_toolbar) {
    if (is_super_admin() || current_user_can('editor'))
        return $wp_toolbar;

    $wp_toolbar->remove_node('my-sites');
    $wp_toolbar->remove_node('notes');
    $wp_toolbar->remove_node('gdbb-toolbar-info');
    $wp_toolbar->remove_node('site-name');
    $wp_toolbar->remove_node('wpseo-menu');
    $wp_toolbar->remove_node('new-content');
    $wp_toolbar->remove_node('comments');
}

function customLoginLogo() {
    echo '<style type="text/css">
	h1 a { 
            background-image: none !important; 
        }
	</style>';
}

add_action('login_head', __NAMESPACE__ . '\customLoginLogo');

function openIdAuthorLink($link, $author_id, $author_nicename) {
    $user = get_user_by("id", $author_id);

    if (!$user)
        $user = get_user_by("slug", $author_nicename);

    $file = home_url('/');
    $link = $file . '?author=' . $user->user_login;

    return $link;
}

add_filter('author_link', __NAMESPACE__ . '\openIdAuthorLink', 10, 3);

// OPEN ID PROVIDER FOR ALL 
function openIdHasCap($caps, $cap) {
    //[TODO] should we add checks ?
    //if ($cap == "use_openid_provider" || (is_array($cap) && isset($cap["use_openid_provider"]))) {
    $caps['use_openid_provider'] = true; // maybe we should use another value?
    //}

    return $caps;
}

add_filter('role_has_cap', __NAMESPACE__ . '\openIdHasCap', 10, 2);
add_filter('user_has_cap', __NAMESPACE__ . '\openIdHasCap', 10, 2);

/*
  SIMPLE PRESS
 */

add_filter('sph_PostIndexMyCred', function ($out) {
    return str_replace(">MyCred ", ">", $out); // hack to hide MyCred prefix
});

add_filter('sph_PostIndexMyCred_args', function ($a) {
    $a["icon"] = "";

    return $a;
});

add_action('sph_BeforeSectionEnd_action', function() {
    global $spThisPost, $spThisUser;

    if ($spThisUser->admin) {
        sp_AddButton('tagClass=spButton spRight&icon=sp-logo-tiny.png&link=' . urlencode('/wp-admin/users.php?page=mycred-edit-balance&user_id=' . $spThisPost->user_id . '&ctype=mycred_default'), __('Adjust AZP'), __('Adjust Azeroth Points for this user'), 0, 'spLogoButton');
    }
});

add_action('sph_BeforeSectionEnd_profileDetails', function() {
    global $spThisUser, $spProfileUser;

    if (!$user = get_userdata($spProfileUser->ID))
        return false;

    if (!$me = get_userdata($spThisUser->ID))
        return false;

    echo "<div style='width:200px;'>";
    sp_AddButton('tagClass=spButton spLeft&icon=sp-logo-tiny.png&link=' . urlencode('/membri/' . $user->user_login), __('Profilo Sito'), __('Profilo principale del sito'), 0, 'spLogoButton');

    if ($spProfileUser->ID != $spThisUser->ID) {

        sp_AddButton('tagClass=spButton spLeft&icon=sp-logo-tiny.png&link=' . urlencode('/membri/' . $me->user_login . '/messages/compose/?r=' . $user->user_login), __('Messaggio ( PM )'), __('Invia un messaggio privato all\'utente ( PM )'), 0, 'spLogoButton');
        //sp_AddButton('tagClass=spButton spLeft&icon=sp-logo-tiny.png&link=' . urlencode('/membri/'.$me->user_login.'/friends/add-friend/'.$spProfileUser->ID.'/'), __('Aggiungi come amico'), __('Aggiungi questo utente come tuo amico all\'interno del sito'), 0, 'spLogoButton');
    }
    echo "</div>";
});
