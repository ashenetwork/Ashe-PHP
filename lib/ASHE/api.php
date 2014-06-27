<?php

class ASHE_API {
  public static $mode = null;
  public static $merchant_id = null;
  public static $private_key = null;
  public static $version = '1';
  public static $base_url = 'https://www.ashepay.com';
  public static $format = 'array';

  public static function mode($mode = null) {
    self::$mode = $mode;
  }

  public static function merchant_id($merchant_id = null) {
    self::$merchant_id = $merchant_id;
  }

  public static function private_key($private_key = null) {
    self::$private_key = $private_key;
  }

  public static function format($format = 'array') {
    self::$format = $format;
  }

  public static function post_request($params=array(), $method=null) {
    self::check_params($params, $method);
    $url = self::build_url($method);
    $data = curl_init($url);
    $data = self::build_headers($data);
    curl_setopt($data, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($data, CURLOPT_POSTFIELDS, json_encode(self::build_private_data($params)));
    curl_setopt($data, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($data);
    curl_close($data);
    if ($response === FALSE or strpos($response, "<title>502 Bad Gateway</title>") != FALSE) {
      throw new ASHEError("Could not connect to the server. Please check your internet connection.", "E500");
    }
    else {
      $response = utf8_encode($response);
      $response = self::format_response($response);
      return $response;
    }
  }

  private static function check_params($params, $method) {
    if (!in_array($method, array("charge", "refund"))) {
      throw new Exception("Unknown method.");
    }
    if (self::$merchant_id == null) {
      throw new ASHEError("Invalid merchant id.", "E402");
    }
    if (self::$private_key == null) {
      throw new ASHEError("Invalid private key.", "E402");
    }
    if (!in_array(self::$mode, array("sandbox", "production"))) {
      throw new ASHEError("Invalid mode. Please specify either 'production' or 'sandbox'.", "E402");
    }
    if (!$params['amount']) {
      throw new ASHEError("Invalid amount.", "E401");
    }
    if ($method == 'charge' and !$params['token']) {
      throw new ASHEError("Invalid token.", "E401");
    }
    if ($method == 'refund' and $params['transaction_id'] != '0' and !$params['transaction_id']) {
      throw new ASHEError("Invalid transaction id.", "E401");
    }
  }

  private static function build_url($method) {
    if ($method == 'charge') {
      if (self::$mode == 'production') {
        $url = self::$base_url . "/api/payment/v1/" . self::$merchant_id . "/";
      }
      else {
        $url = self::$base_url . "/api/sandbox/" . self::$merchant_id . "/";
      }
    }
    elseif ($method == 'refund') {
      if (self::$mode == 'production') {
        $url = self::$base_url . "/api/refund/v1/" . self::$merchant_id . "/";
      }
      else {
        $url = self::$base_url . "/api/sandbox/refund/" . self::$merchant_id . "/";
      }
    }
    return $url;
  }

  private static function build_headers($data) {
    $header = array("content-type:application/json","Accept: application/json");
    curl_setopt($data, CURLOPT_HTTPHEADER, $header);
    curl_setopt($data, CURLOPT_USERAGENT, "ASHE Corporation Ruby Payment API V" . self::$version);
    return $data;
  }

  private static function build_private_data($params) {
    $params['merchant_id'] = self::$merchant_id;
    $params['private_key'] = self::$private_key;
    return $params;
  }

  private static function format_response($response) {
    if (self::$format == 'json') {
      $array_response = self::response_to_array($response);
      $json_response = json_encode($response);
      return $json_response;
    }
    else { //Array
      $array_response = self::response_to_array($response);
      return $array_response;
    }
  }

  private static function response_to_array($response) {
    $response = json_decode($response, true);
    $array_response = array();
    foreach($response as $key => $value) {
      $array_response[$key] = $value;
    }
    if (isset($array_response['errors']) and is_array($array_response['errors']) and count($array_response['errors']) > 0) {
      $error = $array_response['errors'][0];
      throw new ASHEError($error['msg'], $error['code']);
    }
    return $array_response;
  }

}

