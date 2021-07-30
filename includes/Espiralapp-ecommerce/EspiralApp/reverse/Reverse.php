<?php 

namespace EspiralApp;

class Reverse extends EspiralAppResource
{
  public function reverse($transaction)
  {
    $body = array_merge($transaction);
    $response = parent::sendReverse('reverse', $body);
    return $response;
  }
}
