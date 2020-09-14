<?php 

namespace BelugaPay;

class Cart extends BelugaPayResource3D
{
  public function sign($cardHolder, $address, $transaction, $itms, $currency = 'MX', $redirectUrl = '', $metadata = [])
  {
    $body = array_merge($cardHolder, $address, $transaction, $itms, $currency, $redirectUrl, $metadata);
    $response = parent::createSign('cart/sign/', $body);
    return $response;
  }
}
