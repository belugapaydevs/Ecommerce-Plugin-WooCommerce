<?php 

namespace EspiralApp;

class Sales extends EspiralAppResource
{
  public function sale($cardHolder, $card, $address, $transaction, $metadata = [])
  {
    $body = array_merge($cardHolder, $card, $address, $transaction, $metadata);
    $response = parent::sendSale('sale', $body);
    return $response;
  }
}
