<?php 

namespace BelugaPay;

class Cancel extends BelugaPayResource
{
  public function cancel($transaction)
  {
    $body = array_merge($transaction);
    $response = parent::sendCancel('cancel', $body);
    return $response;
  }
}
