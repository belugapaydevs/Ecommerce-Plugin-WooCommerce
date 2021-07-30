<?php

namespace EspiralApp;

use \EspiralApp\Token;
use \EspiralApp\Commerce;
use \EspiralApp\Request3D;

class EspiralAppResource3D
{
  protected function createSign($url, $body)
  {
    $requestor = new Request3D();
    $requestor->setHeaders(array(
      "Content-Type: application/json",
      "cache-control: no-cache"
    ));
    $body = array_merge($body);

    $response = $requestor->request('POST', $url, null, $body);
    return $response;
  }
}