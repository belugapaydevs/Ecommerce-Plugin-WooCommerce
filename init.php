<?php
/*
  Plugin Name: Belugapay Checkout Gateway for Woocommerce
  Description: Payment gateway for credit and debit cards for visa or mastercard.
  Version: 0.0.1
  Author: Belugapay
  Author URI: https://belugapay.com/
  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if (!class_exists('BelugaPay')) {
  require_once 'includes/Belugapay-ecommerce/BelugaPay.php';
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
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=belugapaycardpayment' ) . '">' . __( 'Configure', 'wc-gateway-belugapay' ) . '</a>'
	);
	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_belugapay_gateway_plugin_links' );

add_action( 'plugins_loaded', 'wc_belugapay_gateway_init', 11 );

function wc_belugapay_gateway_init() {

  class WC_Belugapay_Card_Payment extends WC_Payment_Gateway {

    public function __construct() {
  
      $this->id                 = 'belugapaycardpayment';
      $this->icon               = '';
      $this->has_fields         = true;
      $this->method_title       = __( 'Belugapay Card Payment', 'belugapaycardpayment' );
      $this->method_description = __( 'Allow payments by credit or debit card, visa or mastercard that are allowed in Mexican' );
      $this->enabled            = false;
      
      // Load the settings.
      $this->init_form_fields();
      $this->init_settings();
      
      // Define user set variables
      $this->title        = $this->get_option( 'title' );
      $this->description  = $this->get_option( 'description' );
      $this->instructions = $this->get_option( 'instructions', $this->description );

      $this->sandboxApiKey = $this->settings['sandboxApiKey'];
      $this->productionApiKey = $this->settings['productionApiKey'];
      $this->enabledProduction = $this->settings['enabledProduction'];
      
      // Actions
      add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
      add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
      
      // Customer Emails
      add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
    }

    /**
		 * Initialize Gateway Settings Form Fields
		 */
		public function init_form_fields() {
	  
			$this->form_fields = array(
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'wc-gateway-belugapay' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable belugapay Payment', 'wc-gateway-belugapay' ),
					'default' => 'no'
				),
				
				'title' => array(
					'title'       => __( 'Title', 'wc-gateway-belugapay' ),
					'type'        => 'text',
					'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'wc-gateway-belugapay' ),
					'default'     => __('Card Payment', 'wc-gateway-belugapay'),
					'desc_tip'    => true,
        ),

				'enabledProduction' => array(
					'title'   => __( 'Enable Production', 'wc-gateway-belugapay' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable API KEY Production', 'wc-gateway-belugapay' ),
					'default' => 'no'
				),
				
				'sandboxApiKey' => array(
          'title'       => __('Sandbox API KEY', 'wc-gateway-belugapay'),
          'type'        => 'password',
          'default'     => __('', 'wc-gateway-belugapay')
        ),

        'productionApiKey' => array(
          'title'       => __('Production API KEY', 'wc-gateway-belugapay'),
          'type'        => 'password',
          'default'     => __('', 'wc-gateway-belugapay')
        ),
			);
    }

    public function payment_fields() {
      include_once('templates/cardPayment.php');
    }

    function ecommerceTransaction($order_id, $order) {
      /* Uncomment is only development */
      // \BelugaPay\Environment::setEnvironment('develop');
      // \BelugaPay\BelugaPay::init();

      if ($this->enabledProduction === 'yes') {
        \BelugaPay\User::setApiKey($this->productionApiKey);
      } else {
        \BelugaPay\User::setApiKey($this->sandboxApiKey);
      }

      $sale = new \BelugaPay\Sales();

      $orderData = $order->get_data();

      $cardHolder = array(
        'cardHolder' => array (
          'name' => $orderData['billing']['first_name'] . ' ' . $orderData['billing']['last_name'],
          'email' => $orderData['billing']['email'],
          'phone' => '+52' . $orderData['billing']['phone']
        )
      );
      $card = array(
        'card' => array (
          'card' => $_POST['cardNumber'],
          'expires' => $_POST['expirationMonth'] .'/'. $_POST['expirationYear'],
          'cvv' => $_POST['cvv'],
        )
      );
      $address = array(
        'address' => array (
          'country' => 'MX',
          'state' => $orderData['billing']['state'],
          'city' => $orderData['billing']['city'],
          'numberExt' => 'NA',
          'numberInt' => '',
          'zipCode' => $orderData['billing']['postcode'],
          'street' => $orderData['billing']['address_1'],
        )
      );
      $metadata = array(
        'metadata' => array (
          'WooCommerceId' => $order_id
        )
      );
      $transaction = array(
        'saleTransaction' => array (
          'amount' => $orderData['total']
        )
      );

      $response = $sale->sale($cardHolder, $card, $address, $transaction, $metadata);

      if (
        !isset($response['codigo'])   ||
        $response['codigo'] !== 200   ||
        !isset($response['mensaje'])  ||
        $response['mensaje'] === 'Declinada'
      ) {
        if (isset($response['message'])) {
          throw new Exception($response['message']);
        }
        throw new Exception('Tarjeta declinada');
      }

      return $response;
    }

    function process_payment( $order_id ) {
      global $woocommerce;
      $order = new WC_Order( $order_id );

      // file_put_contents(
      //   'debugBelugapay.txt',
      //   file_get_contents('debugBelugapay.txt') . json_encode($order->get_data()) . json_encode($_POST)
      // );

      try {
        $response = $this->ecommerceTransaction($order_id, $order);
        if ($response) {
          $order->payment_complete();
          $woocommerce->cart->empty_cart();

          update_post_meta($order->get_id(), 'transactionId', $response['data']['transaction']['transactionId']);
          update_post_meta($order->get_id(), 'reference', $response['data']['transaction']['reference']);
          update_post_meta($order->get_id(), 'authCode', $response['data']['transaction']['authCode']);

          $order->add_order_note( sprintf(
            "Transaction Id: %s,<br/>Reference: %s,<br/>Authorization Code: %s",
            $response['data']['transaction']['transactionId'],
            $response['data']['transaction']['reference'],
            $response['data']['transaction']['authCode']
          ));
        }
    
        // Return thankyou redirect
        return array(
          'result' => 'success',
          'redirect' => $this->get_return_url( $order )
        );
      } catch (Exception $e) {
        global $wp_version;

        if (version_compare($wp_version, '4.1', '>=')) {
          wc_add_notice(__('Error: ', 'wc-gateway-belugapay') . $e->getMessage(), $notice_type = 'error');
        } else {
          $woocommerce->add_error(__('Error: ', 'wc-gateway-belugapay') . $e->getMessage());
        }

        $order->add_order_note( sprintf(
          "Error Transaction : '%s'",
          $e->getMessage()
        ));
      }
    }

    /**
     * Output for the order received page.
     */
    public function thankyou_page() {
      if ( $this->instructions ) {
          echo wpautop( wptexturize( $this->instructions ) );
      }
    }

    public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
      if ( $this->instructions && ! $sent_to_admin && 'belugapaycardpayment' === $order->payment_method && $order->has_status( 'on-hold' ) ) {
        echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
      }
    }
  }
}

?>