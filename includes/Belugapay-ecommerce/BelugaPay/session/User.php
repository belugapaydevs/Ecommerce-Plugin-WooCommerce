<?php 

namespace BelugaPay;

class User
{
  public static $apiKey = '';

  public static function setApiKey($apiKey)
  {
    self::$apiKey = $apiKey;
  }

  public static function getApiKey()
  {
    return self::$apiKey;
  }
}
