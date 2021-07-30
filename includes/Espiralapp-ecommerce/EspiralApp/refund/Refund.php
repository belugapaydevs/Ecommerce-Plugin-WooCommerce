<?php 

namespace EspiralApp;

class Refund extends EspiralAppResource
{
  public function refund($transaction)
  {
    $body = array_merge($transaction);
    $response = parent::sendRefund('refund', $body);
    return $response;
  }
}
