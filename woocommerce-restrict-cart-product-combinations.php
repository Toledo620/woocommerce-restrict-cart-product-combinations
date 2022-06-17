<?php
/*
Plugin Name: Woocommerce-restrict-cart-product-combinations
Description: Restricts cart product combinations
Plugin URI:  N/A
Author:      toledo, daigo75
Version:     1.0
License:     GPLv2 or later

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version
2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
with this program. If not, visit: https://www.gnu.org/licenses/
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Include plugin dependencies: admin and public
// Check if WooCommerce is active
$plugin = 'woocommerce/woocommerce.php';
if (
	! in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) &&
	! ( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
) {
	return;
}

  // Step 1 - Keep track of cart contents
  add_action('wp_loaded', function() {
  // This variable must be global, we will need it later. If this code were
  // packaged as a plugin, a property could be used instead
  global $allowed_cart_items;
  // If this array of product names is in the cart, all other products cannot be added to it
  $restricted_cart_items = array(
    'Crypto Donation'
  );
  
  // Get main instance of WC cart
  $cart = WC()->cart;
  if ($cart == null){ 
      return;
  }
  
  foreach($cart->get_cart() as $item) {
    if(in_array($item['data']->get_name(), $restricted_cart_items)) {
      $allowed_cart_items[] = $item['data']->get_name();
    }
  }

  add_filter('woocommerce_is_purchasable', function($is_purchasable, $product) {
    global $allowed_cart_items;
    // If any of the restricted products is in the cart, any other must be made
    // "not purchasable"
    if(!empty($allowed_cart_items)) {
      $is_purchasable = in_array($product->get_name(), $allowed_cart_items);
    }
    return $is_purchasable;
  }, 10, 2);
}, 10);

// Step 3 - Explain customers why they can't add some products to the cart
add_filter('woocommerce_get_price_html', function($price_html, $product) {
  if(!$product->is_purchasable()) {
    $price_html .= '<p>' . __('This product cannot be purchased together with current cart products.', 'woocommerce') . '</p>';
  }
  return $price_html;
}, 10, 2);

