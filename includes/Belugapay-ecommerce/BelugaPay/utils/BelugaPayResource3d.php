<?php

namespace BelugaPay;

use \BelugaPay\Token;
use \BelugaPay\User;
use \BelugaPay\Commerce;
use \BelugaPay\Request3D;

class BelugaPayResource3D
{
  protected function createSign($url, $body)
  {
    $requestor = new Request3D();
    $requestor->setHeaders(array(
      "Content-Type: application/json",
      "cache-control: no-cache"
    ));
    $body = array_merge($body);

    $response = $requestor->request('POST', $url . User::$apiKey, null, $body);
    return $response;
  }
}