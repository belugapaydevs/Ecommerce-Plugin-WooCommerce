<?php 

namespace BelugaPay;

class Reverse extends BelugaPayResource
{
  public function reverse($transaction)
  {
    $body = array_merge($transaction);
    $response = parent::sendReverse('reverse', $body);
    return $response;
  }
}
