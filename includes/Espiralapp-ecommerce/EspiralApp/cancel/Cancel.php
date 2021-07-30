<?php 

namespace EspiralApp;

class Cancel extends EspiralAppResource
{
  public function cancel($transaction)
  {
    $body = array_merge($transaction);
    $response = parent::sendCancel('cancel', $body);
    return $response;
  }
}
