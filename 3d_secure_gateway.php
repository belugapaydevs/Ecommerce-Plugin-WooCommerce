<?php

class WC_Belugapay_3dsecure_Payment extends WC_Payment_Gateway {

  public function __construct() {

    $this->id                 = 'belugapay3dsecurepayment';
    $this->icon               = '';
    $this->has_fields         = true;
    $this->method_title       = __( 'Belugapay 3d Secure Payment', 'belugapay3dsecurepayment' );
    $this->method_description = __( 'Allow payments by credit or debit card, visa or mastercard that are allowed in Mexican' );
    $this->enabled            = false;
    $this->supports           = array( 'products', 'refunds' );
    
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
    $this->redirectSuccess = $this->settings['redirectSuccess'];
    
    // Actions
    add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
    add_action( 'woocommerce_api_securedata', array( $this,'callback_handler_securedata'));
    
    // Customer Emails
    add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
  }

  function callback_handler_securedata() {
    $_POST = json_decode(file_get_contents('php://input'), true);

    global $woocommerce;
    $order = new WC_Order( sanitize_text_field((string) $_POST['request']['metadata']['idOrder']) );
    $order->payment_complete();

    $woocommerce->cart->empty_cart();

    update_post_meta($order->get_id(), 'transactionId', sanitize_text_field((string) $_POST['response']['data']['transaction']['transactionId']));
    update_post_meta($order->get_id(), 'reference', sanitize_text_field((string) $_POST['response']['data']['transaction']['reference']));
    update_post_meta($order->get_id(), 'authCode', sanitize_text_field((string) $_POST['response']['data']['transaction']['authCode']));

    $order->add_order_note( sprintf(
      "Transaction Id: %s,<br/>Reference: %s,<br/>Authorization Code: %s",
      sanitize_text_field((string) $_POST['response']['data']['transaction']['transactionId']),
      sanitize_text_field((string) $_POST['response']['data']['transaction']['reference']),
      sanitize_text_field((string) $_POST['response']['data']['transaction']['authCode'])
    ));

    return true;
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

      'redirectSuccess' => array(
        'title'       => __( 'Success page URL', 'wc-gateway-belugapay' ),
        'type'        => 'text',
        'description' => __( 'URL to redirect the customer when the order is paid.', 'wc-gateway-belugapay' ),
        'default'     => __(get_bloginfo('url'), 'wc-gateway-belugapay'),
        'desc_tip'    => true,
      ),
    );
  }

  public function payment_fields() {
    include_once('templates/cardPayment3d.php');
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

    $cart = new \BelugaPay\Cart();

    $orderData = $order->get_data();

    $cardHolder = array(
      'cardHolder' => array (
        'name' => $_POST['cardHolder'],
        'email' => $orderData['billing']['email'],
        'phone' => '+52' . $orderData['billing']['phone']
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

    $order_items = wc_get_order( $order_id );
    foreach($order_items->get_items() as $item_id => $item) {
      $items_data['items'][] = array(
        'name' => $item->get_name(),
        'price' => $item->get_subtotal(),
        'description' => '',
        'quantity' => $item->get_quantity(),
      );
    }

    if (floatval($orderData['shipping_total']) > 0) {
      $items_data['items'][] = array(
        'name' => 'Tarifa de envio',
        'price' => $orderData['shipping_total'],
        'description' => '',
        'quantity' => 1,
      );   
    }

    $transaction = array(
      'transaction' => array (
        'total' => $orderData['total']
      )
    );

    $metadata = array(
      'metadata' => array(
        'idOrder' => $order_id
      ));

    $currency = array (
      'currency' => 'MXN'
    );
    
    $redirectUrl = array (
      'redirectUrl' => $this->redirectSuccess,
      'backPage' => get_bloginfo('url'),
      'redirectData' => array (
        'url' => get_bloginfo('url') . '/?wc-api=securedata',
        'redirectMethod' => 'POST',
      )
    );

    $response = $cart->sign($cardHolder, $address, $transaction, $items_data, $currency, $redirectUrl, $metadata);

    if (!isset($response['token'])) {
      throw new Exception('Tarjeta declinada');
    }

    return $response;
  }

  function process_payment( $order_id ) {
    global $woocommerce;
    $order = new WC_Order( $order_id );

    try {
      // file_put_contents(dirname(__FILE__) . '/logs.txt', '21321');
      $response = $this->ecommerceTransaction($order_id, $order);
      if ($response) {
        $order->update_status('on-hold');

        $order->save();

        $config = \BelugaPay\Environment::getEnvironment();

        return array(
          'result' => 'success',
          'redirect' => $config['apiBaseCart'] . 'cart/checkout3d/' . $response['token']
        );
      }
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

  function ecommerceTransactionRefund($order_id, $reference) {
    /* Uncomment is only development */
    // \BelugaPay\Environment::setEnvironment('develop');
    // \BelugaPay\BelugaPay::init();

    if ($this->enabledProduction === 'yes') {
      \BelugaPay\User::setApiKey($this->productionApiKey);
    } else {
      \BelugaPay\User::setApiKey($this->sandboxApiKey);
    }

    $cancel = new \BelugaPay\Cancel();

    $cancelTransaction = array(
      'saleTransaction' => array (
        'reference' => $reference
      )
    );

    $response = $cancel->cancel($cancelTransaction);

    if (
      !isset($response['codigo'])   ||
      $response['codigo'] !== 200   ||
      !isset($response['mensaje'])  ||
      $response['mensaje'] !== 'Aprobada'
    ) {
      if (isset($response['message'])) {
        throw new Exception($response['message']);
      }
      throw new Exception('Error al realizar la cancelación');
    }

    return $response;
  }

  public function process_refund($order_id, $amount = null, $reason = '') {
    $order = new WC_Order( $order_id );
    $orderData = $order->get_data();
    $reference = get_post_meta($order_id, 'reference', true);

    if ($orderData['total']  !== $amount ) {
      return new WP_Error( 'error', __( 'Error de cancelacion: El monto debe ser igual al monto de la orden.',
                  'wc-gateway-belugapay' ) );
    }

    try {
      $response = $this->ecommerceTransactionRefund($order->get_id(), $reference);

      if ($response) {

        $order->add_order_note( sprintf(
          "Cancellation: %s,<br/>Reference: %s,<br/>Authorization Code: %s",
          $response['data']['transaction']['transactionId'],
          $response['data']['transaction']['reference'],
          $response['data']['transaction']['authCode']
        ));

        update_post_meta($order->get_id(), 'cancellationTransactionId', $response['data']['transaction']['transactionId']);
        update_post_meta($order->get_id(), 'cancellationReference', $response['data']['transaction']['reference']);
        update_post_meta($order->get_id(), 'cancellationAuthCode', $response['data']['transaction']['authCode']);
  
        return true;
      }

      return new WP_Error( 'error', __( 'Error al cancelar la transacción', 'wc-gateway-belugapay' ) );
    } catch (Exception $e) {
      global $wp_version;

      $order->add_order_note( sprintf(
        "Error Transaction : '%s'",
        $e->getMessage()
      ));

      return new WP_Error( 'error', __( 'Error: ' . $e->getMessage(), 'wc-gateway-belugapay' ) );
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
    if ( $this->instructions && ! $sent_to_admin && 'belugapay3dsecurepayment' === $order->payment_method && $order->has_status( 'on-hold' ) ) {
      echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
    }
  }
}
?>