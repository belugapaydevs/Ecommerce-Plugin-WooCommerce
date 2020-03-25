<?php 

namespace BelugaPay;

class Refund extends BelugaPayResource
{
  public function refund($transaction)
  {
    $body = array_merge($transaction);
    $response = parent::sendRefund('refund', $body);
    return $response;
  }
}
