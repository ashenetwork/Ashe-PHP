<?php

class ASHEError extends Exception {
  public function __construct($message, $errorCode = "0") {
    parent::__construct($message);
    $this -> errorCode = $errorCode;
  }

  public function __toString() {
    return __CLASS__ . ": [{$this->errorCode}]: {$this->message}\n";
  }

  public function getErrorCode() {
    return $this-> errorCode;
  } 
}
