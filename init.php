<?php
/*
  Plugin Name: Espiralapp Checkout Gateway for Woocommerce
  Description: Payment gateway for credit and debit cards of visa or mastercard type allowed in Mexico.
  Version: 1.0.0
  Author: Espiralapp
  Author URI: https://espiralapp.com/
  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if (!class_exists('EspiralApp')) {
  require_once 'includes/Espiralapp-ecommerce/EspiralApp.php';
}

if ( !in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	return;
}

/**
 * Add the gateway to WC Available Gateways
 * 
 * @since 1.0.0
 * @param array $gateways all available WC gateways
 * @return array $gateways all WC gateways + belugapay gateway
 */
function wc_belugapay_card_payment_add_to_gateways( $gateways ) {
  $gateways[] = 'WC_Belugapay_Card_Payment';
  $gateways[] = 'WC_Belugapay_3dsecure_Payment';
  $gateways[] = 'WC_Belugapay_santander_Payment';

	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'wc_belugapay_card_payment_add_to_gateways' );

/** 
 * Adds plugin page links
 * 
 * @since 1.0.0
 * @param array $links all plugin links
 * @return array $links all plugin links + our custom links (i.e., "Settings")
 */
function wc_belugapay_gateway_plugin_links( $links ) {
	$plugin_links = array(
    '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=belugapaycardpayment' ) . '">' . __( 'Configure Card Payment', 'wc-gateway-belugapay' ) . '</a>',
    '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=belugapay3dsecurepayment' ) . '">' . __( 'Configure 3D Secure', 'wc-gateway-belugapay' ) . '</a>',
    '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=belugapaysantanderpayment' ) . '">' . __( 'Configure Espiralapp - Santander', 'wc-gateway-belugapay' ) . '</a>'
	);
	return array_merge( $plugin_links, $links );
}
// configuracion
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_belugapay_gateway_plugin_links' );

function wc_belugapay_gateway_init(){
  include_once('card_gateway.php');
  include_once('3d_secure_gateway.php');
  // include_once('mit_gateway.php');
}

// cual va a ejecutar.
add_action( 'plugins_loaded', 'wc_belugapay_gateway_init', 11 );

?>