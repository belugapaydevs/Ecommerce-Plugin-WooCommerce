<?php

namespace BelugaPay;

use \BelugaPay\Environment;

abstract class BelugaPay
{
	public static $apiKey = '';
	public static $apiBase = '';
	public static $apiBaseCart = '';
	public static $apiVersion = '';
	public static $version = '';

	public static function init()
	{
		$config = Environment::getEnvironment();
		self::$version = $config['version'];
		self::$apiVersion = $config['apiVersion'];
		self::$apiBase = $config['apiBase'];
		self::$apiBaseCart = $config['apiBaseCart'];
	}

	public static function setApiBase($apiBase)
	{
		self::$apiBase = $apiBase;
	}

	public static function getApiBase()
	{
		return self::$apiBase;
	}

	public static function setApiBaseCart($apiBaseCart)
	{
		self::$apiBaseCart = $apiBaseCart;
	}

	public static function getApiBaseCart()
	{
		return self::$apiBaseCart;
	}
}
