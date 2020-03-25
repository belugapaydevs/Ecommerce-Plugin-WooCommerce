<?php

namespace BelugaPay;

use \BelugaPay\Token;
use \BelugaPay\User;
use \BelugaPay\Commerce;
use \BelugaPay\Request;

class BelugaPayResource
{
  protected function sendSale($url, $body)
  {
    $requestor = new Request();
    $requestor->setHeaders(array(
      "Content-Type: application/json",
      "cache-control: no-cache"
    ));
    $commerceInfo = array(
      'apikey' => array (
        'key' => User::$apiKey
      ));
    $body = array_merge($body, $commerceInfo);
    $response = $requestor->request('POST', $url, null, $body);
    return $response;
  }

  protected function sendCancel($url, $body)
  {
    $requestor = new Request();
    $requestor->setHeaders(array(
      "Content-Type: application/json",
      "cache-control: no-cache"
    ));
    $commerceInfo = array(
      'apikey' => array (
        'key' => User::$apiKey
      ));
    $body = array_merge($body, $commerceInfo);
    $response = $requestor->request('POST', $url, null, $body);
    return $response;
  }

  protected function sendRefund($url, $body)
  {
    $requestor = new Request();
    $requestor->setHeaders(array(
      "Content-Type: application/json",
      "cache-control: no-cache"
    ));
    $commerceInfo = array(
      'apikey' => array (
        'key' => User::$apiKey
      ));
    $body = array_merge($body, $commerceInfo);
    $response = $requestor->request('POST', $url, null, $body);
    return $response;
  }

  protected function sendReverse($url, $body)
  {
    $requestor = new Request();
    $requestor->setHeaders(array(
      "Content-Type: application/json",
      "cache-control: no-cache"
    ));
    $commerceInfo = array(
      'apikey' => array (
        'key' => User::$apiKey
      ));
    $body = array_merge($body, $commerceInfo);
    $response = $requestor->request('POST', $url, null, $body);
    return $response;
  }
}