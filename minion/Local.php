<?php

namespace Minion;

Class Local
{
  
  public static function get($table = null) {
    
   if (!isset($_SESSION['shop'])) {

      return [];

    }
    
    $path = DIR_STORES . $_SESSION['shop'] . '/' . $table . '.json';

    if (!file_exists($path)) {

      file_put_contents($path, '');

    }
    
    $string = file_get_contents($path);
    $json = json_decode($string, true);

    return $json ? $json : [];

  }

  public static function put($table, $data) {

    if (!isset($_SESSION['shop'])) {

      return;

    }

    $path = DIR_STORES . $_SESSION['shop'] . '/' . $table . '.json';

    file_put_contents($path , json_encode($data));

  }

  public static function queue($params) {

    file_put_contents(DIR_QUEUE . '/' . $_SESSION['shop'] . '_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.json', json_encode($params));

  }

}