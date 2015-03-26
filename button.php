<?php

/* DON'T CHANGE THIS FILE!
 * Use this file only as draft.
 * Copy this file into the folder wp-content/easy-digital-shop
 * and make your changes there.
 */

echo '<p>';
echo self::get_eds_symbol($currency) . $price . ' <br />';
echo '<input type="image" src="https://www.paypal.com/en_GB/i/btn/btn_buynow_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">';
echo '</p>';