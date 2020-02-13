<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ E_DEPRECATED);

require __DIR__ . '/../vendor/autoload.php';

use Minion\Helpers;
use Minion\Shopify;
use Minion\Integrations;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

define('DIR_STORES', __DIR__ . '/../stores/');
define('DIR_QUEUE', __DIR__ . '/../queue/');

$dotenv = Dotenv\Dotenv::create(__DIR__ . '/../');
$dotenv->load();

// SMTP
define('SMTP', [
  'port' => getenv('SMTP_PORT'),
  'host' => getenv('SMTP_HOST'),
  'encryption' => getenv('SMTP_ENCRYPTION'),
  'authentication' => getenv('SMTP_AUTHENTICATION'),
  'username' => getenv('SMTP_USERNAME'),
  'password' => getenv('SMTP_PASSWORD')
]);

$files = Helpers::filescan(DIR_QUEUE);

$found = false;
foreach ($files as $filename) {
  $fileinfo = pathinfo($filename);

  if ($fileinfo['filename'][0] != '_' && !$found) {
    $found = $filename;
  }
}

if ($found) {
    $fileinfo = pathinfo($found);
    $rename = '_' . $fileinfo['basename'];
    $newpath = $fileinfo['dirname'] . '/' . $rename;
    rename($filename, $newpath);

    $data = json_decode(file_get_contents($newpath), true);

    $_SESSION['shop'] = $data['shop'];

    switch ($data['type']) {
      case 'email':

        $sent = true;

        try {
          Helpers::email($data['data']);
        } catch (Exception $e) {
          $sent = false;
          echo $e->getMessage();
        }

        if ($sent) {
          unlink($newpath);
        }

      break;

      case 'mailchimp':

        $result = Integrations::mailchimp('subscribe', $data['data']);
        
        if ($result) {
          unlink($newpath);
        } else {
          echo 'Mailchimp error';
        }

      break;

      case 'klaviyo':

        $result = Integrations::klaviyo('subscribe', $data['data']);
        
        if ($result) {
          unlink($newpath);
        } else {
          echo 'Klaviyo error';
        }

      break;

      case 'nexmo':

        $result = Integrations::nexmo('sms', $data['data']);
        
        if ($result) {
          unlink($newpath);
        } else {
          echo 'Nexmo error';
        }

      break;

    }

}