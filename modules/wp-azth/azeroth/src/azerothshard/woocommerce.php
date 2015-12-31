<?php

use Azth as Azth;

require_once "ServerReg.php";

// Add WooCommerce customer username to edit/view order admin page
add_action('woocommerce_admin_order_data_after_billing_address', 'woo_display_order_username', 10, 1);

function woo_display_order_username($order) {
    global $post;

    $customer_user = get_post_meta($post->ID, '_customer_user', true);
    echo '<p><strong style="display: block;">' . __('Customer Username') . ':</strong> <a href="user-edit.php?user_id=' . $customer_user . '">' . get_userdata($customer_user)->user_login . '</a></p>';
}

// avoid some emails to admin
add_action('woocommerce_email_enabled_new_order',function ($enabled, $order) {
    $items=$order->get_items();

    $enabled=false;

    foreach ($items as $item) {
        // enable mails if there are product different by this list:
        if ($item["product_id"] != PRODUCTID_I80) 
            $enabled=true;
    }


    return $enabled;
}, 10, 2);

// Allow exe or dmg for digital downloads
add_filter('upload_mimes', function($mimetypes, $user) {
    // Only allow these mimetypes for admins or shop managers
    $manager = $user ? user_can($user, 'manage_woocommerce') : current_user_can('manage_woocommerce');

    if ($manager) {
        $mimetypes = array_merge($mimetypes, [
            'lua' => 'application/octet-stream'
        ]);
    }

    return $mimetypes;
}, 10, 2);

function azth_woocommerce_payment_complete($order_id) {
    $order = new WC_Order($order_id);
    $items = $order->get_items();

    $dumpPath="../../data/dumps/";
    
    // variation id => pg name
    $pgList=array(
        1553 => "Ptroguew",
        1552 => "Ptwarwprot",
        1551 => "Ptwarwfury",
        1550 => "Ptwarwarms",
        1549 => "Ptwarlokw",
        1548 => "Ptshwresto",
        1547 => "Ptshwenha",
        1546 => "Ptshwele",
        1545 => "Ptptwshadow",
        1544 => "Ptptwdisci",
        1543 => "Ptplwprot",
        1542 => "Ptplwretry",
        1541 => "Ptplwholy",
        1540 => "Ptmgw",
        1539 => "Pthunterw",
        1538 => "Ptddwtank",
        1537 => "Ptddwresto",
        1536 => "Ptddwferal",
        1535 => "Ptddwbalance",
        1534 => "Ptdkwblood",
        1533 => "Ptdkwfrost"
    );

        
    $wpdb = new wpdb(AZTH_DB_USER,AZTH_DB_PASS,AZTH_DB_AUTH,AZTH_DB_HOST);

    foreach ($items as $item) {
        $product_name = $item['name'];
        $productId = $item['product_id'];

        if ($productId==PRODUCTID_I80) {
            $product_variation_id = $item['variation_id'];

            $dump=$pgList[$product_variation_id];

            $current_user = wp_get_current_user();

            $id = $wpdb->get_var("SELECT id FROM account WHERE username  = '".$current_user->user_login."';");


            Azth\executeSoapCommand('pdump write ' . $dumpPath.$dump . ' ' . $dump);

            Azth\executeSoapCommand('pdump load ' . $dumpPath.$dump . ' ' . $current_user->user_login);

            $wpdb->query("
	            UPDATE `azth_1_chars`.characters
	            SET at_login = 192
	            WHERE account = $id 
		            AND name = '$dump'
            ");

            /*
                Azth\executeSoapCommand('character rename ' . $newName);

                Azth\executeSoapCommand('character changefaction ' . $newName);

                Azth\executeSoapCommand('character customize ' . $newName);
            */
        }
    }
}

add_action('woocommerce_payment_complete', 'azth_woocommerce_payment_complete');

function azth_woocommerce_check_cart_items( $order_id ){
   $items = WC()->cart->get_cart();
        
    $wpdb = new wpdb(AZTH_DB_USER,AZTH_DB_PASS,AZTH_DB_AUTH,AZTH_DB_HOST);

    foreach($items as $item => $values) { 
        $product = $values['data']->post; 
        $productId = $product->ID;

        if ($productId==PRODUCTID_I80) {
            $current_user = wp_get_current_user();

            $id = $wpdb->get_var("SELECT id FROM account WHERE username  = '".$current_user->user_login."';");

            $cnt = $wpdb->get_var($wpdb->prepare("SELECT COUNT(name) FROM `azth_1_chars`.characters WHERE account = %d", $id) );
            @mysql_close( $wpdb->dbh ); 

            if ($cnt > 9) {
                wc_add_notice( sprintf( __("Hai troppi pg nel tuo account! non puoi acquistare: %s", 'woocommerce'), $product->post_title ), 'error' );
            }

            return;
        }
    }
}
add_action( 'woocommerce_check_cart_items', 'azth_woocommerce_check_cart_items');


/**
 * 
 * @param type $bool
 * @param WC_Coupon $instance
 */
function azth_woocommerce_coupon_is_valid($bool, $instance ) {
    try {
        if ($instance->code==COUPON_I80) {
            $wpdb = new wpdb(AZTH_DB_USER,AZTH_DB_PASS,AZTH_DB_AUTH,AZTH_DB_HOST);

            $current_user = wp_get_current_user();

            $id = $wpdb->get_var("SELECT id FROM account WHERE username  = '".$current_user->user_login."';");

            if ($id>0) {
                $minlevel=19;
                $maxLevel=60;
                $cnt = $wpdb->get_var($wpdb->prepare("SELECT COUNT(name) FROM `azth_1_chars`.characters WHERE account = %d AND LEVEL > %d", $id, $maxLevel) );

                if ($cnt != 0) {
                    add_filter( 'gettext', function ( $translated_text, $text, $text_domain ) use ($maxLevel,$minlevel) {
	                    if ( "Coupon is not valid." === $text ) {
		                    return "Hai già alcuni personaggi di livello > $maxLevel ! Questo coupon è riservato ai nuovi utenti con almeno un personaggio compreso tra il livello $minlevel e $maxLevel!";
	                    }

	                    return $translated_text;
                    }, 10, 3 );


                    return false;
                } 

                $cnt = $wpdb->get_var($wpdb->prepare("SELECT COUNT(name) FROM `azth_1_chars`.characters WHERE account = %d AND level <= %d AND level>= %d", $id, $maxLevel, $minlevel) );
                @mysql_close( $wpdb->dbh ); 

                if (!$cnt) {
                    add_filter( 'gettext', function ( $translated_text, $text, $text_domain ) use ($maxLevel,$minlevel) {
	                    if ( "Coupon is not valid." === $text ) {
		                    return "Devi avere almeno un personaggio compreso tra il livello $minlevel e $maxLevel per beneficiare di questo coupon!";
	                    }

	                    return $translated_text;
                    }, 10, 3 );


                    return false;
                } 
                    


                return true;
            }

            return false;
        }

        return $bool;
    } catch (Exception $e) {
        die($e->getMessage());
    }
}

add_action('woocommerce_coupon_is_valid','azth_woocommerce_coupon_is_valid',10, 2);



/*
    ENABLE ADMIN BAR
*/
add_filter('woocommerce_disable_admin_bar', 'azth_wc_disable_admin_bar', 10, 1);
 
function azth_wc_disable_admin_bar($prevent_admin_access) {
    //if (!current_user_can('example_role')) {
    //    return $prevent_admin_access;
    //}
    return false;
}
 
add_filter('woocommerce_prevent_admin_access', 'azth_wc_prevent_admin_access', 10, 1);
 
function azth_wc_prevent_admin_access($prevent_admin_access) {
    //if (!current_user_can('example_role')) {
    //    return $prevent_admin_access;
    //}
    return false;
}


/*
function woocommerceCustomFields($checkout) {

    echo '<div id="item-id"><h2>' . __('Item Id') . '</h2>';

    \woocommerce_form_field('item_id', array(
        'type' => 'text',
        'class' => array('item-id-class form-row-wide'),
        'label' => __('Inserisci l\'id dell\'item desiderato, fai attenzione che sia del livello ( item level ) giusto!'),
        'placeholder' => __('Inserisci l\'id'),
        'required' => true
            ), $checkout->get_value('item_id'));

    echo '</div>';

    echo '<div id="character-name"><h2>' . __('Nome del personaggio') . '</h2>';

    \woocommerce_form_field('character_name', array(
        'type' => 'text',
        'class' => array('character-name-class form-row-wide'),
        'label' => __('Inserisci il nome del personaggio al quale inviarlo'),
        'placeholder' => __('Nome del personaggio'),
        'required' => true
            ), $checkout->get_value('character_name'));

    echo '</div>';
}
*/

/**
 * Add the field to the checkout
 */
//add_action('woocommerce_after_order_notes', __NAMESPACE__ . '\woocommerceCustomFields');
