<?php 

namespace BelugaPay;

use \BelugaPay\BelugaPay;

class Request {

  private $headers = '';

  function __construct()
  {}

  public function setHeaders($headers)
  {
    $this->headers = $headers;
  }

  public static function apiUrl($url = '')
  {
    $apiBase = BelugaPay::$apiBase;
    return $apiBase . $url;
  }

  private function buildQueryParamsUrl($url, $params)
  {
    if(!is_null($params)){
      $params = http_build_query($params);
      $url = $url.'?'.$params;
    }

    return $url;
  }

  private function buildSegmentParamsUrl($url, $params)
  {
    if(!is_array($params)){
      $url = $url.urlencode($params);
    }

    return $url;
  }

  public function request($method, $url, $params = null, $body = null)
  {
    // $jsonParams = json_encode($params);
    $headers = $this->headers;
    $curl = curl_init();
    $method = strtoupper($method);
    $opts = array();
    $jsonBody = json_encode($body);

    $url = $this->buildQueryParamsUrl($url, $params);

    $url = $this->apiUrl($url);
    $opts[CURLOPT_URL] = $url;

    $opts[CURLOPT_CUSTOMREQUEST] = $method;
    $opts[CURLOPT_CONNECTTIMEOUT] = 30;
    $opts[CURLOPT_TIMEOUT] = 80;
    $opts[CURLOPT_RETURNTRANSFER] = true;
    $opts[CURLOPT_HTTPHEADER] = $headers;
    // $opts[CURLOPT_SSL_VERIFYHOST] =  0; // remove
    // $opts[CURLOPT_SSL_VERIFYPEER] =  0; // remove
    $opts[CURLOPT_HTTP_VERSION] =  CURL_HTTP_VERSION_1_1;
    $opts[CURLOPT_POSTFIELDS] = $jsonBody;

    curl_setopt_array($curl, $opts);
    $response = curl_exec($curl);
    $info = curl_getinfo($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
      throw new \Exception($err);
    } 

    $jsonResponse = json_decode($response, true);

    return $jsonResponse;
  }
}
