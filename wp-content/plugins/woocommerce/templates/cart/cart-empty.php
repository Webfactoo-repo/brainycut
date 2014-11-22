<?php
/**
 * Empty cart page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

wc_print_notices();

if (is_user_logged_in()) {
	$current_user = wp_get_current_user();
	$href = icl_get_home_url() . 'members/' . $current_user->user_login . '/course/';
	
}else{
	$href = icl_get_home_url() . 'all-courses/';
}

?>

<p class="cart-empty"><?php _e( 'Your cart is currently empty.', 'woocommerce' ) ?></p>

<?php do_action( 'woocommerce_cart_is_empty' ); ?>

<p class="return-to-shop"><a class="button wc-backward" href="<?php  echo $href;?>"><?php _e( 'Courses', 'vibe' ) ?></a></p>

