<?php

namespace Minion;

use Minion\Local;
use \DrewM\MailChimp\MailChimp;

Class Integrations
{

  public static function check($integration)
  {

    $integrations = Local::get('integrations');

    if (isset($integrations[$integration]) && isset($integrations[$integration]['active']) && $integrations[$integration]['active']) {
      if (isset($integrations[$integration]['valid']) && $integrations[$integration]['valid']) {
        return true;
      }
    }

    return false;

  }

  public static function mailchimp($action = 'validate', $data = [])
  {

    switch($action) {

      /*================================================================
      =            CHECK IF MAILCHIMP CREDENTIALS ARE VALID            =
      ================================================================*/
      case 'validate':

        if (strpos($data['apikey'], '-') === false) {
          return false;
        }

        list(, $dc) = explode('-', $data['apikey']);
        $list_id = $data['listId'];
        $url = "https://{$dc}.api.mailchimp.com/3.0/lists/{$list_id}";

        $response = \Requests::get($url, ['Authorization' => 'apikey ' . $data['apikey']]);
        $res = json_decode($response->body);

        if (isset($res->name)) {
          return true;
        } else {
          return false;
        }

        break;

      /*=============================================================
      =            SUBSCRIBE NEW EMAIL TO MAILCHIMP LIST            =
      =============================================================*/
      case 'subscribe':

        if (strpos($data['apikey'], '-') === false) {
          return false;
        }

        list(, $dc) = explode('-', $data['apikey']);
        $list_id = $data['listId'];
        $url = "https://{$dc}.api.mailchimp.com/3.0/lists/$list_id/members";

        $response = \Requests::post($url, ['Authorization' => 'apikey ' . $data['apikey']], json_encode([
          'email_address' => $data['email'],
          'status'        => 'subscribed'
        ]));
        $res = json_decode($response->body);

        if (isset($res->errors)) {
          return false;
        } else {
          return true;
        }
        break;

    }

  }

  public static function klaviyo($action = 'validate', $data = [])
  {

    switch($action) {

      /*================================================================
      =            CHECK IF MAILCHIMP CREDENTIALS ARE VALID            =
      ================================================================*/
      case 'validate':

        $list_id = $data['listId'];
        $url = "https://a.klaviyo.com/api/v2/list/{$list_id}?api_key=" . $data['apikey'];

        $response = \Requests::get($url);
        $res = json_decode($response->body);

        if (isset($res->list_name)) {
          return true;
        } else {
          return false;
        }

        break;

      /*=============================================================
      =            SUBSCRIBE NEW EMAIL TO MAILCHIMP LIST            =
      =============================================================*/
      case 'subscribe':

        $list_id = $data['listId'];
        $url = "https://a.klaviyo.com/api/v2/list/{$list_id}/members?api_key=" . $data['apikey'];

        $headers = array('Content-Type' => 'application/json');
        $response = \Requests::post($url, $headers, json_encode(['profiles' => [
          'email' => $data['email'],
        ]]));
        $res = json_decode($response->body);

        if (isset($res->detail) || isset($res->message) || isset($res->messages)) {
          return false;
        } else {
          return true;
        }
        break;

    }

  }

  public static function nexmo($action = 'validate', $data = [])
  {

    switch($action) {

      /*============================================================
      =            CHECK IF NEXMO CREDENTIALS ARE VALID            =
      ============================================================*/
      case 'validate':

        $query = http_build_query([
          'api_key' => $data['apikey'],
          'api_secret' => $data['secret']
        ]);
        $url = "https://rest.nexmo.com/account/numbers?" . $query;

        $response = \Requests::get($url);
        $res = json_decode($response->body);

        if (isset($res->{'error-code'})) {
          return false;
        } else {
          return true;
        }

        break;

      /*=============================================
      =            SEND SMS NOTIFICATION            =
      =============================================*/
      case 'sms':

        $basic  = new \Nexmo\Client\Credentials\Basic($data['apikey'], $data['secret']);
        $client = new \Nexmo\Client($basic);

        $message = $client->message()->send([
          'from' => isset($data['from']) && $data['from'] != '' ? substr(str_replace(' ', '', $data['from']), 0, 11) : 'Alert',
          'to' => str_replace(['+', ' ', '-', '(', ')'], '', $data['to']),
          'text' => $data['text']
        ]);

        if ($message->getStatus() == '0') {
          return true;
        } else {
          return false;
        }

        break;

    }

  }

  public static function twilio($action = 'validate', $data = [])
  {

    switch($action) {

      /*============================================================
      =            CHECK IF NEXMO CREDENTIALS ARE VALID            =
      ============================================================*/
      case 'validate':

        $sid = $data['apikey'];
        $token = $data['secret'];
        $client = new \Twilio\Rest\Client($sid, $token);

        try {
          $reads = $client->calls->read();
          return true;
        } catch(\Exception $e) {
          return false;
        }

        break;

      /*=============================================
      =            SEND SMS NOTIFICATION            =
      =============================================*/
      case 'sms':

        $sid = $data['apikey'];
        $token = $data['secret'];
        $client = new \Twilio\Rest\Client($sid, $token);

        try {
          $client->messages->create(
            $data['to'], ['from' => $data['from'], 'body' => $data['text']]
          );
          
          return true;

        } catch(\Exception $e) {
          
          return false;

        }

        break;

    }

  }
  
}