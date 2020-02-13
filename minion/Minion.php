<?php

namespace Minion;

use Minion\Local;
use Minion\Shopify;
use Minion\Helpers;
use \Firebase\JWT\JWT; 

Class Minion
{

  public $routes = [];
  public $app = [];

  public function __construct()
  {

    if (isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])) {

      $_SESSION['shop'] = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];

    } else {

      $parts = parse_url($_SERVER['REQUEST_URI']);

      if (isset($parts['query'])) {

        parse_str($parts['query'], $query);
        $_SESSION['shop'] = $query['shop'];

      }

    }

    $classname = '\App\\app';
    $this->app = new $classname($this);

  }

  public function route($action, \Closure $callback)
  {

    $action = trim($action, '/');
    $this->routes[$action] = $callback;

  }

  public function view($view, $data)
  {

    if ($view != 'install') {
      $data['assets'] = $this->getAppAssets();
    }
    
    $path = DIR_PUBLIC . 'views/' . $view . '.php';

    ob_start();
    include($path);
    $var=ob_get_contents(); 
    ob_end_clean();

    echo $var;
    die();

  }

  public function setToken()
  {

    $tokenId    = base64_encode(uniqid());
    $issuedAt   = time();
    $notBefore  = $issuedAt;
    $expire     = $notBefore + 7200;
    $serverName = HOST;

    $data = [
      'iat'  => $issuedAt,
      'jti'  => $tokenId,
      'iss'  => $serverName,
      'nbf'  => $notBefore,
      'exp'  => $expire
    ];
    $secretKey = base64_decode(API_SECRET);
    return JWT::encode($data, $secretKey,'HS512'); 

  }

  public function checkToken()
  {

    $validateHttp = false;

    if (isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])) {

      $access_token_path = DIR_STORES . $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'] . '/.access_token';
      $validateHttp = true;

      if (!file_exists($access_token_path)) {

        header('HTTP/1.1 401 Unauthorized', true, 401);
        die();

      }

    }

    if (!isset($_SERVER['HTTP_X_TOKEN']) || !$validateHttp) {

      header('HTTP/1.1 401 Unauthorized', true, 401);
      die();
      
    }

    try {

      $secretKey = base64_decode(API_SECRET); 
      JWT::decode($_SERVER['HTTP_X_TOKEN'], $secretKey, ['HS512']);

    } catch (Exception $e) {

      header('HTTP/1.1 401 Unauthorized', true, 401);
      die();

    }

  }

  public function getAppAssets()
  {

    $files = Helpers::filescan(DIR_APP . '/');
    $assets = [];

    foreach ($files as $filename) {
      
      $file = pathinfo($filename);
      $file['dirname'] = str_replace('\\', '/', $file['dirname']);

      if (isset($file['extension']) && $file['extension'] == 'js') {

        if ($file['basename'] != 'scripts.js') {

          $paths = explode('/app/', $file['dirname']);
          $path = end($paths);

          $tag = '<script src="/asset/' . $path . '/' . $file['basename'] . '"></script>';
          $assets[] = $tag;
          
        }

      }

      if (isset($file['extension']) && $file['extension'] == 'css') {

        $paths = explode('/app/', $file['dirname']);
        $path = end($paths);

        $tag = '<link rel="stylesheet" href="/asset/' . $path . '/' . $file['basename'] . '">';
        $assets[] = $tag;

      }

      if (isset($file['extension']) && $file['extension'] == 'vue') {

        $paths = explode('/app/', $file['dirname']);
        $path = end($paths);

        if ($file['filename'][0] == '_') {
          $prefix = 'inc-';
          $dir = str_replace('/', '', str_replace('views', '', $path));
          $filename = ($dir != '' ? $dir . '-' : '') . substr($file['filename'], 1);
        } else {
          $prefix = 'view-';
          $filename = $file['filename'];
        }

        $tag = '<script>Vue.component("' . $prefix . $filename . '", window.httpVueLoader("/asset/' . $path . '/' . $file['basename'] . '"))</script>';
        $assets[] = $tag;

      }

    }
    
    return $assets;

  }

  public function run()
  {

    $uri = explode('?', $_SERVER['REQUEST_URI']);
    $code = $_SERVER['REQUEST_METHOD'] . ' ' . $uri[0];
    $action = trim( $code, '/' );

    if (isset($this->routes[$action])) {

      $callback = $this->routes[$action];
      call_user_func($callback, $this);

    } else {

      // Show assets
      $sub = substr($action, 0, 11);

      if ($sub == 'GET /asset/') {

        $mime_types = [
          'css' => 'text/css'
          ,'js' => 'application/javascript'
          ,'png' => 'image/png'
          ,'jpg' => 'image/jpg'
          ,'gif' => 'image/gif'
          ,'json' => 'application/json'
          ,'vue' => 'text/plain'
        ];

        $ext = explode('.', $action);
        
        header('Content-Type: ' . $mime_types[end($ext)]);
        header('Status: 200');

        if (str_replace($sub, '', $action) == 'tag.js') {

          echo $this->app->scripts();
          die();

        } else {

          readfile(DIR_ROOT . '/app/' . str_replace($sub, '', $action));
          die();

        }

      }

      // Route with param
      foreach ($this->routes as $route => $obj) {
        
        if (strpos($route, '@') !== false) {

          $noparam = str_replace('@', '', $route);

          if (strpos($action, $noparam) !== false) {

            $callback = $this->routes[$route];
            $param = str_replace($noparam, '', $action);

            call_user_func($callback, $this, $param);
            die();

          }
        }
      }

      Helpers::error(404);

    }

    die();

  }

  public function uninstall()
  {
    
    $shop = $_SESSION['shop'];
    $files = Helpers::filescan(DIR_STORES . '/' . $_SESSION['shop']);

    foreach ($files as $filename) {
      $file = pathinfo($filename);

      if ($file['basename'] != 'billing.json') {
        unlink($filename);
      }
    }

    Helpers::dropDb($shop);

  }

}