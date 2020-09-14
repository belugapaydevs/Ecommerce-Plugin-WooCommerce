<?php

namespace BelugaPay;

class Environment {
  private static $env;

  public static function setEnvironment ($mode = '') {
    switch ($mode) {
      case 'develop':
        self::$env = [
          'version' => '0.0.1',
          'apiVersion' => '1.0.44',
          'apiBase' => 'http://transaction-api:8080/api/v2/',
          'apiBaseCart' => 'https://e7a4f34f2960.ngrok.io/',
        ];
        break;
      case 'production':
        self::$env = [
          'version' => '1.0.0',
          'apiVersion' => '1.0.0',
          'apiBase' => 'https://transaction.belugapay.com/api/v2/',
          'apiBaseCart' => 'https://cart.belugapay.app/',
        ];
        break;
      default:
        self::$env = [
          'version' => '1.0.0',
          'apiVersion' => '1.0.0',
          'apiBase' => 'https://transaction.belugapay.com/api/v2/',
          'apiBaseCart' => 'https://cart.belugapay.app/',
        ];
        break;
    }
  }

  public static function getEnvironment() {
    if (!isset(self::$env)) {
      self::setEnvironment();
    } 
    return self::$env;
  }
}
