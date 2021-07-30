<?php 

use \EspiralApp\User;
namespace EspiralApp;

class Cart extends EspiralAppResource3D
{
  public function sign(
    $cardHolder,
    $address,
    $transaction,
    $itms,
    $currency = array('currency' => 'MXN'),
    $redirectUrl = array (),
    $metadata = [],
    $bankingSource = array('bankingSource' => '1')
  )
  {
    $body = array_merge(
      $cardHolder,
      $address,
      $transaction,
      $itms,
      $currency,
      $redirectUrl,
      $metadata,
      $bankingSource
    );
    $response = parent::createSign('cart/generalSing?key=' . User::$apiKey, $body);
    return $response;
  }
}
