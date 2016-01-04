<?php

namespace Azth;

class MainTest extends \Enhance\TestFixture {

    public function testMainFunctions() {
        $balance = mycred_coupon_min_balance(90000, 3063); // balance for coupon
        // Assert
        if (!is_user_logged_in())  // if user is not logged in then balance should be zero
            \Enhance\Assert::areIdentical(0, $balance);
        else {
            // implement test...
        }
    }

}
