<?php
class WC_Belugapay_Card_Payment extends WC_Payment_Gateway {

  public function __construct() {

    $this->id                 = 'belugapaycardpayment';
    $this->icon               = '';
    $this->has_fields         = true;
    $this->method_title       = __( 'Belugapay Card Payment', 'belugapaycardpayment' );
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

    $cardHolderText = sanitize_text_field((string) $_POST['cardHolder']);
    $cardNumber = sanitize_text_field((string) $_POST['cardNumber']);
    $expirationMonth = sanitize_text_field((string) $_POST['expirationMonth']);
    $expirationYear = sanitize_text_field((string) $_POST['expirationYear']);
    $cvv = sanitize_text_field((string) $_POST['cvv']);

    $cardHolder = array(
      'cardHolder' => array (
        'name' => $cardHolderText,
        'email' => sanitize_text_field((string) $orderData['billing']['email']),
        'phone' => '+52' . sanitize_text_field((string) $orderData['billing']['phone'])
      )
    );
    $card = array(
      'card' => array (
        'card' => $cardNumber,
        'expires' => $expirationMonth .'/'. $expirationYear,
        'cvv' => $cvv,
      )
    );
    $address = array(
      'address' => array (
        'country' => 'MX',
        'state' => sanitize_text_field((string) $orderData['billing']['state']),
        'city' => sanitize_text_field((string) $orderData['billing']['city']),
        'numberExt' => 'NA',
        'numberInt' => '',
        'zipCode' => sanitize_text_field((string) $orderData['billing']['postcode']),
        'street' => sanitize_text_field((string) $orderData['billing']['address_1']),
      )
    );
    $metadata = array(
      'metadata' => array (
        'WooCommerceId' => $order_id
      )
    );
    $transaction = array(
      'saleTransaction' => array (
        'amount' => sanitize_text_field((string) $orderData['total'])
      )
    );

    $response = $sale->sale($cardHolder, $card, $address, $transaction, $metadata);

    // file_put_contents(
    //   'debugBelugapay.txt',
    //   file_get_contents('debugBelugapay.txt') . json_encode($response)
    // );

    if (
      !isset($response['codigo'])   ||
      $response['codigo'] !== 200   ||
      !isset($response['mensaje'])  ||
      $response['mensaje'] !== 'Aprobada'
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

        // Return thankyou redirect
        return array(
          'result' => 'success',
          'redirect' => $this->get_return_url( $order )
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
    if ( $this->instructions && ! $sent_to_admin && 'belugapaycardpayment' === $order->payment_method && $order->has_status( 'on-hold' ) ) {
      echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
    }
  }
}
?>