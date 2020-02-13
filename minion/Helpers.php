<?php

namespace Minion;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Cocur\Slugify\Slugify;
use MongoDB\Client as Mongo;

Class Helpers
{

  public static function error($code, $msg = '', $id = '')
  {

    header('HTTP/1.1 ' . $code . ' ' . $msg, true, $code);
    echo json_encode(['error' => $code, 'message' => $msg, 'id' => $id]);
    die();

  }

  public static function success()
  {

    header('HTTP/1.1 200 OK', true, 200);
    echo json_encode(['success' => true]);
    die();
    
  }

  public static function json($array)
  {

    header('Content-type: application/json');
    echo json_encode($array); 
    die();

  }

  public static function redirect($url)
  {

    header('X-Frame-Options: GOFORIT');
    header('Location: ' . $url, true, 302);
    die();

  }

  public static function slugify($string)
  {

    $slugify = new Slugify();
    return $slugify->slugify($string); 

  }

  public static function filescan($dir, &$results = [])
  {

    $files = scandir($dir);

    foreach($files as $key => $value){
      $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
      if(!is_dir($path)) {
        $results[] = $path;
      } else if($value != "." && $value != "..") {
        Helpers::filescan($path, $results);
        $results[] = $path;
      }
    }

    return $results;

  }

  public static function email($params, $smtp = false)
  {

    $smtp = $smtp ? $smtp : SMTP;

    if ($smtp == false) {

      $subject = $params['subject'];
      $headers = "From: " . $params['from'] . "\r\n";
      $headers .= "MIME-Version: 1.0\r\n";
      $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $to = is_array($params['email']) ? $params['email'][0] : $params['email'];

      if (is_array($params['email'])) {

        array_shift($params['email']);
        $emailList = implode(',', $params['email']);
        $headers .= "Bcc: $emailList\r\n";

      }

      $body = $params['body'];

      if (mail($to, $subject, $body, $headers)) {
        echo "Message sent.";
      } else {
        echo "Message could not be sent.";
      }

    } else {

      $mail = new PHPMailer;
      $mail->isSMTP();
      $mail->SMTPDebug = 2;
      $mail->Port = isset($smtp['port']) ? (int) $smtp['port'] : 587;
      $mail->Host = isset($smtp['host']) ? $smtp['host'] : '';
      $mail->SMTPSecure = isset($smtp['encryption']) ? $smtp['encryption'] : ''; 

      if (isset($smtp['authentication']) && $smtp['authentication'] == '1') {

        $mail->SMTPAuth = true;
        $mail->Username = isset($smtp['username']) ? $smtp['username'] : '';
        $mail->Password = isset($smtp['password']) ? $smtp['password'] : '';

      }

      $from = isset($params['from']) ? $params['from'] : $smtp['username'];
      $fromName = isset($params['name']) ? $params['name'] : '';

      $mail->setFrom($from, $fromName);

      if (is_array($params['email'])) {

        foreach ($params['email'] as $key => $email) {
          $mail->addBCC($email);
        }

      } else {

        $mail->addAddress($params['email']);

      }

      $mail->Subject = $params['subject'];
      $mail->Body = $params['body'];
      $mail->isHTML(true);

      if(!$mail->send()) {  
        throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
      } else {
        echo "Message has been sent";
      }

    }

  }

  public static function db()
  {

    $mongo = new Mongo();
    $shop = APP_HANDLE . '__' . str_replace('.', '_', $_SESSION['shop']);

    return $mongo->$shop;

  }

  public static function dropDb($shop)
  {

    $mongo = new Mongo();
    $mongo->dropDatabase(APP_HANDLE . '__' . str_replace('.', '_', $shop));

  }

  public function mid($id) {

    return new \MongoDB\BSON\ObjectId($id);

  }
  
}