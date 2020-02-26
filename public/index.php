<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type, x-xsrf-token, x_csrftoken, Cache-Control, X-Requested-With, _token');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ E_DEPRECATED);

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(__DIR__ . '/../');
$dotenv->load();

define('ENV', getenv('ENV'));
define('APP_HANDLE', getenv('APP_HANDLE'));

define('DIR_PUBLIC', __DIR__ . '/');
define('DIR_ROOT', __DIR__ . '/../');
define('DIR_MINION', __DIR__ . '/../minion/');
define('DIR_APP', __DIR__ . '/../app/');
define('DIR_STORES', __DIR__ . '/../stores/');
define('DIR_QUEUE', __DIR__ . '/../queue/');

// Get keys from App settings in Partner Dashboard
define('API_VERSION', getenv('API_VERSION'));
define('API_KEY', getenv('API_KEY'));
define('API_SECRET', getenv('API_SECRET'));
define('HOST', getenv('HOST'));

// Scopes for apps: remove or add depending on your needs
define('SCOPE', getenv('SCOPE'));

// Other APIs
define('BITLY', getenv('BITLY'));

session_start();

use Minion\Helpers;
use Minion\Local;
use Minion\Shopify;
use Minion\Minion;
use Minion\Billing;

$minion = new Minion();

$minion->route('GET /', function($minion) {

  $params = $_GET;

  if (!isset($params['shop'])) {
    Helpers::error(401);
  }

  $shop = (isset($params['shop']) ? $params['shop'] : null);

  if (!isset($params['aftercallback'])) {

    if (!isset($params['hmac'])) {
      Helpers::error(401);
    }

    $valid_hmac = Shopify::validateHmac($params);
    $valid_shop = Shopify::validateShopDomain($params['shop']);

    if (!$valid_hmac || !$valid_shop) {
      Helpers::error(401);
    }
  }

  $access_token_path = DIR_STORES . $params['shop'] . '/.access_token';

  if (file_exists($access_token_path)) {

    $token = $minion->setToken();

    if (isset($_GET['charge_id'])) {
      Billing::activate($_GET['charge_id']);
      $view = 'billing';
    } else {
      $view = 'index';
    }

    Billing::check();

    $minion->view('admin', ['shop' => $params['shop'], 'host' => HOST, '_token' => $token, 'view' => $view, 'billing_plan' => Billing::plan()]);

  }

  $redirectUri = HOST . '/auth/shopify/callback';
  $installUrl = 'https://' . $params['shop'] . '/admin/oauth/authorize?client_id=' . API_KEY . '&scope=' . SCOPE . '&redirect_uri=' . $redirectUri;

  Helpers::redirect($installUrl);

});

$minion->route('GET /auth/shopify/callback', function($minion) {

  $params = $_GET;
  $valid_hmac = Shopify::validateHmac($params);
  $valid_shop = Shopify::validateShopDomain($params['shop']);
  $access_token = '';

  if ($valid_hmac && $valid_shop) {

    $access_token = Shopify::getAccessToken($params['shop'], $params['code']);

  } else {

    // This request is NOT from Shopify
    Helpers::error(404);

  }

  if (!file_exists(DIR_STORES . $params['shop'])) {

    mkdir(DIR_STORES . $params['shop'], 0777, true);
    
  }

  file_put_contents(DIR_STORES . $params['shop'] . '/.access_token', $access_token);

  if (!file_exists(DIR_STORES . $params['shop'] . '/billing.json')) {

    Local::put('billing', ['name' => 'Free']);
    
  }

  if (!file_exists(DIR_STORES . $params['shop'] . '/limits.json')) {

    Local::put('limits', ['notifications' => [
      'date' => date('Y-m-d\TH:i:s'),
      'value' => 0
    ]]);
    
  }

  $minion->app->install();

  $homeUrl = HOST . '/?aftercallback=true&shop=' . $params['shop'];
  Helpers::redirect($homeUrl);

});

$minion->route('POST /api/shopify', function($minion) {

  $minion->checkToken();

  $data = json_decode(file_get_contents('php://input'), true);

  if (isset($data['endpoint'])) {

    $request = Shopify::request($data['endpoint'], $data['params'], $data['method']);

    Helpers::json(json_decode($request));

  }

  Helpers::error(404);

});

$minion->route('GET /app/@', function($minion, $url) {

  if (isset($_SESSION['shop'])) {

    $minion->checkToken();
    $minion->app->router($url);

  }

  Helpers::success();

});

$minion->route('POST /app/@', function($minion, $url) {

  if (isset($_SESSION['shop'])) {

    $paths = array_filter(explode('/', $url));

    if (isset($paths[0]) && $paths[0] == 'webhook') {

      //Shopify::validateWebhook();

    } else {

      $minion->checkToken();

    }

    $minion->app->router(join($paths, '/'));

  }

  Helpers::success();

});

$minion->route('POST /app', function($minion) {

  if (isset($_SESSION['shop'])) {

    $params = $_REQUEST;
    $valid_hmac = Shopify::validateSignature($params);
    $valid_shop = Shopify::validateShopDomain($params['shop']);

    if (!$valid_hmac || !$valid_shop) {
      Helpers::error(401);
    }

    $minion->app->router('call');

  }

  Helpers::success();

});

$minion->route('GET /export/@', function($minion, $url) {

  header("Content-Type: text/csv; charset=UTF-16LE");
  header('Status: 200');
  readfile(__DIR__ . '/export/' . $url);
  die();

});

$minion->route('GET /billing/@', function($minion, $url) {

  $paths = array_filter(explode('/', $url));

  if ($paths[0] == 'plans') {

    Helpers::json(Billing::plan('all'));

  }

});

$minion->route('POST /billing/@', function($minion, $url) {

  $minion->checkToken();

  $paths = array_filter(explode('/', $url));
  $data = json_decode(file_get_contents('php://input'), true);

  if ($paths[0] == 'upgrade') {

    $res = Billing::create($data);

    if (!isset($res['errors'])) {

      $confirmation = Billing::charge($res['recurring_application_charge'], $data['plan']);
      Helpers::json(['redirect' => $confirmation]);

    } else {

      Helpers::error(400);

    }

  }

  if ($paths[0] == 'cancel') {

    Billing::cancel();

    Helpers::json(['plan' => [
      'label' => 'Free',
      'value' => null,
      'trial' => false,
      'status' => 'active'
    ]]);

  }

});

$minion->route('POST /uninstall', function($minion) {

  if (isset($_SESSION['shop'])) {

    Shopify::validateWebhook();
    $minion->uninstall();

  }

  Helpers::success();

});

$minion->run();