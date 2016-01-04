<?php

namespace Azth;

function mycred_coupon_min_balance($post_meta, $post_id = 0) {
    // this is useful when we want to create a coupon with total as min requirement
    switch ($post_id) {
        case 3063:
            // workaround for christmas coupon
            $user_total = intval(do_shortcode('[mycred_total_balance]'));
            $post_meta = intval($post_meta);

            if ($user_total >= $post_meta)
                return 1;
            break;
        default:
            return $post_meta;
    }

    return 0;
}

add_filter('mycred_coupon_min_balance', AZTH_NS . 'mycred_coupon_min_balance', 10, 2);

//add_filter('mycred_run_this', 'azthStaffPayout');

function azthStaffPayout($request) {

    $staff = array(
            //"Yehonal" => 10000,  // Yehonal
            //"cipo" => 15000 // alberto
    );

    // Only applicable for recurring payouts
    if ($request['ref'] != 'recurring_payout')
        return $request;

    // Get user role
    $user_id = absint($request['user_id']);
    $user = get_userdata($user_id);

    // Set various roles recurring points payouts.
    // ADMIN get 0 points
    //if (in_array('administrator', $user->roles))
    //    $request['amount'] = 0;

    if (array_key_exists($user->user_login, $staff))
        $request['amount'] = $staff[$user->user_login];

    return $request;
}

/**
 * Monthly payouts
 * On the first page load on the first day of each month
 * award 10 points to all users with the role "Subscriber".
 * @version 1.0
 */
/*
add_action( 'mycred_init', 'azthMonthlyPayouts' );
function azthMonthlyPayouts() {

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;

	$this_month = date( 'n' );
	if ( get_option( 'mycred_monthly_payout', 0 ) != $this_month ) {

		// Grab all users for the set role
		$users = get_users( array(
			'role'   => 'administrator', // The role
			'fields' => array( 'ID' )
		) );

		// If users were found
		if ( $users ) {

			$type = 'mycred_default';
			$mycred = mycred( $type );

			// Loop though users
			foreach ( $users as $user ) {

				// Make sure user is not excluded
				if ( $mycred->exclude_user( $user->ID ) ) continue;

				// Make sure users only get this once per month
				if ( $mycred->has_entry( 'monthly_payout', $this_month, $user->ID, '', $type ) ) continue;

				// Payout
				$mycred->add_creds(
					'monthly_payout',
					$user->ID,
					1000,
					'Monthly %_plural% payout',
					$this_month,
					'',
					$type
				);
			}

			update_option( 'mycred_monthly_payout', $this_month );

		}

	}

}
*/
