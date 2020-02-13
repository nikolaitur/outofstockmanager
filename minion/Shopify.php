<?php

namespace Minion;

Class Shopify
{

  public static function validateShopDomain( $shop )
  {

    $substring = explode( '.', $shop );

    if ( count( $substring ) != 3 ) {

      return FALSE;

    }

    $substring[0] = str_replace( '-', '', $substring[0] );

    return ( ctype_alnum( $substring[0] ) && $substring[1] . '.' . $substring[2] == 'myshopify.com' );

  }

  public static function validateHmac($params)
  {

    $hmac = $params['hmac'];
    unset( $params['hmac']);
    ksort($params);

    $computedHmac = hash_hmac('sha256', http_build_query($params), API_SECRET);

    return hash_equals($hmac, $computedHmac);

  }

  public static function validateWebhook()
  {

    $data = file_get_contents('php://input');
    $hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];

    $calculated_hmac = base64_encode(hash_hmac('sha256', $data, API_SECRET, true));

    if (!hash_equals($hmac_header, $calculated_hmac)) {

      header('HTTP/1.1 401 Unauthorized', true, 401);
      die();

    }

  }

  public static function validateSignature($params)
  {

    $hmac = $params['signature'];
    unset( $params['signature']);
    ksort($params);

    $dataString = array();
    foreach ($params as $key => $value) {
      $dataString[] = "{$key}={$value}";
    }

    $string = implode("", $dataString);
    $computedHmac = hash_hmac('sha256', $string, API_SECRET);

    return hash_equals($hmac, $computedHmac);

  }

  public static function getAccessToken($shop, $code)
  {

    $query = [
      'client_id' => API_KEY,
      'client_secret' => API_SECRET,
      'code' => $code
    ];

    $access_token_url = "https://{$shop}/admin/oauth/access_token";

    $curl = curl_init();
    $curlOptions = array(
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_URL => $access_token_url,
      CURLOPT_POSTFIELDS => http_build_query($query)
    );
    curl_setopt_array($curl, $curlOptions);
    $jsonResponse = json_decode( curl_exec($curl), TRUE);
    curl_close($curl);

    return $jsonResponse['access_token'];

  }

  public static function request($resource, $params = [], $method = 'GET', $ignoreErrors = false) {

    $access_token = file_get_contents(DIR_STORES . $_SESSION['shop'] . '/.access_token');

    $url = "https://" . $_SESSION['shop'] . "/admin/api/" . API_VERSION . "/{$resource}";

    $curlOptions = [CURLOPT_RETURNTRANSFER => TRUE];

    if ($method == 'GET') {
      if (!is_null($params)) {
        $url = $url . "?" . http_build_query($params);
      }
    } else {
      $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
    }

    $curlOptions[CURLOPT_URL] = $url;

    $requestHeaders = ['Accept: application/json'];

    if ( $access_token ) {
      $requestHeaders[] = 'X-Shopify-Access-Token: ' . $access_token;
    }

    if ($method == 'POST' || $method == 'PUT') {
      $requestHeaders[] = 'Content-Type: application/json';

      if (!is_null($params)) {
        $curlOptions[CURLOPT_POSTFIELDS] = json_encode($params);
      }
    }

    $curlOptions[CURLOPT_HTTPHEADER] = $requestHeaders;
    $curlOptions[CURLOPT_HEADER] = 1;
    $curlOptions[CURLOPT_RETURNTRANSFER] = 1;

    $curl = curl_init();
    curl_setopt_array($curl, $curlOptions);
    $response = curl_exec($curl);
    curl_close($curl);

    $ex = explode("\r\n\r\n", $response);

    if (isset($ex[0])) {
      $header = $ex[0];
    }

    if (isset($ex[1])) {
      $response = $ex[1];
    }

    $res = json_decode($response, true);

    if (!$res && isset($ex[2])) {
      $response = $ex[2];
    }

    if (isset($res['errors']) && !$ignoreErrors) {
      header('Content-type: application/json');
      $res['resource'] = $resource;
      $res['params'] = $params;
      $res['method'] = $method;
      echo json_encode($res);
      die();
    }

    $next = ''; $prev = '';

    if (strpos($header, 'Link:') !== false) {
      $headerRows = explode("\r\n", $header);
      foreach ($headerRows as $row) {
        if (strpos($row, 'Link:') !== false) {
          $links = explode(',', str_replace('Link: ', '', $row));
          foreach ($links as $link) {
            if (strpos($link, ' rel="previous"') !== false) {
              preg_match("/<(.*)>; rel=\"previous\"/", $link, $pmatches);
              $prev = explode('page_info=', $pmatches[1])[1];
            }
            if (strpos($link, ' rel="next"') !== false) {
              preg_match("/<(.*)>; rel=\"next\"/", $link, $nmatches);
              $next = explode('page_info=', $nmatches[1])[1];
            }
          }
        }
      }
    }

    $combined = '{"next":"' . $next . '", "prev":"' . $prev . '",' . ltrim($response, '{');

    return $combined;


  }

  public static function hook($topic)
  {

    if ($topic != 'app/uninstalled') {
      $address = HOST . '/app/webhook/' . $topic;
    } else {
      $address = HOST . '/uninstall';
    }

    $webhook = [
      'webhook' => [
        'topic' => $topic,
        'address' => $address,
        'format' => 'json'
      ]
    ];

    $response = Shopify::request('webhooks', $webhook, 'POST');
    $response = json_decode( $response, true );

    $id = $response['webhook']['id'];

    $webhooks = Local::get('webhooks');

    $webhooks[] = [
      'id' => $id,
      'topic' => $topic
    ];

    Local::put('webhooks', $webhooks);

  }

  public static function unhook($topic)
  {

    $webhooks = Local::get('webhooks');

    foreach ($webhooks as $key => $webhook) {
      
      if ($webhook['topic'] == $topic) {

        Shopify::request('webhooks/' . $webhook['id'], [], 'DELETE' );
        unset($webhooks[$key]);

      }

    }
    
    Local::put('webhooks', $webhooks);

  }

  public function scripts()
  {

    $script_tag = [
      'script_tag' => [
        'event' => 'onload',
        'src' => HOST . '/asset/tag.js'
      ]
    ];

    self::request('script_tags', $script_tag, 'POST');

  }

  public static function createAssetsTheme()
  {

    $data = [
      'theme' => [
        'name' => 'app-assets-DO_NOT_DELETE'
      ]
    ];

    $theme_json = json_decode(Shopify::request('themes', $data, 'POST'), true);

    if (!isset($theme_json['errors'])) {

      $theme = $theme_json['theme'];

      Local::put('assets_theme', $theme['id']);

    }
    
  }

}