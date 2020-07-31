<?php

namespace Drupal\sym_sgos_data;
  /**
   * Generate 8 random characters salt
   * @return type
   * Generate char salt randomly
   */
class BcryptSaltGenerator{

  public function _btoGetSalt($len){
    $salt = '';
    for($i = 0; $i < $len; $i++){
      $num = rand(0, 2);
      if($num == 0){
        $salt .= chr(rand(48, 57)); // ASCII for numbers
      }
      elseif($num == 1){
        $salt .= chr(rand(65, 90)); // ASCII for capital case letters
      }
      else{
        $salt .= chr(rand(97, 122)); // ASCII for lower case letters
      }
    }
    return $salt;
  }
}
