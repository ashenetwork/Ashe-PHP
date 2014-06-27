<?php

class ASHE_Charge {

  public static function charge($params=array()) {
    $response = ASHE_API::post_request($params, 'charge');
    return $response;
  }

  public static function refund($params=array()) {
    $response = ASHE_API::post_request($params, 'refund');
    return $response;
  }

}
