<?php

namespace App;

use Minion\Local;
use Minion\Shopify;
use Minion\Helpers;
use Minion\Integrations;
use Liquid\Template;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

Class App
{

  public function install()
  {

    Shopify::hook('inventory_levels/update');
    Shopify::hook('app/uninstalled');
    Shopify::scripts();

    $indexes = [
      'created_at' => -1,
      'updated_at' => -1,
      'total_emails' => -1,
      'total_numbers' => -1
    ];

    foreach ($indexes as $key => $value) {
      Helpers::db()->products->createIndex([$key => $value]);
    }

    Helpers::db()->products->createIndex([
      'product.title' => 'text', 'product.id' => 'text', 'variant.sku' => 'text'
    ]);

  }

  public function scripts()
  {

    $scriptFile = file_get_contents(__DIR__ . '/scripts.js');
    $customization = Local::get('customization');
    $params = $customization ? $customization : $this->defaultStyleConfig();

    $code = str_replace('[[config]]', json_encode($params), $scriptFile);

    if (ENV == 'dev') {

      return $code;
      
    } else {

      $uglify = new \GK\JavascriptPacker($code);
      return $uglify->pack();

    }

  }

  public function router($route)
  {

    switch ($route) {

      case 'stats':
        $this->stats();
        break;

      case 'products':
        $this->products();
        break;

      case 'subscriptions':
        $this->subscriptions();
        break;

      case 'remove':
        $this->remove();
        break;

      case 'templates':
        $this->templates();
        break;

      case 'template':
        $this->template();
        break;

      case 'template/preview':
        $this->templatePreview();
        break;

      case 'template/save':
        $this->templateSave();
        break;

      case 'template/remove':
        $this->templateRemove();
        break;

      case 'customization':
        $this->customization();
        break;

      case 'customization/save':
        $this->customizationSave();
        break;

      case 'integrations':
        $this->integrations();
        break;

      case 'integrations/update':
        $this->integrationsUpdate();
        break;

      case 'call':
        switch ($_REQUEST['action']) {

          case 'add':
            $this->add($_REQUEST);
            break;

          case 'track':
            $this->track($_REQUEST);
            break;

        }
        break;

      case 'webhook/inventory_levels/update':
        $this->webhook();
        break;

      case 'export':
        $this->export();
        break;

    }
    
  }

  private function defaultStyleConfig()
  {

    return [
      'email_active' => true,
      'number_active' => false,
      'labels' => [
        'title' => 'Let me know when the product is available',
        'desc' => 'We will send you a notification as soon as this product is back in stock',
        'email' => 'Email address',
        'number' => 'Phone number',
        'submit' => 'Submit',
        'success' => 'Thank you - your request has been submitted!',
        'email_invalid' => 'Your email address is invalid',
        'number_invalid' => 'Your phone number is invalid',
        'email_used' => 'Email address was already used for this product',
        'number_used' => 'Phone number was already used for this product',
      ],
      'style' => [
        'frame_border_color' => '#e9ecef',
        'frame_padding' => 24,
        'title_color' => '',
        'title_size' => 20,
        'desc_color' => '',
        'desc_size' => 16,
        'label_color' => '',
        'label_size' => 14,
        'input_border_color' => '#e9ecef',
        'input_size' => 14,
        'submit_color' => '#ffffff',
        'submit_bg_color' => '#000000',
        'submit_hover_color' => '#ffffff',
        'submit_hover_bg_color' => '#4c4c4c',
        'submit_size' => 14,
        'submit_padding' => '16px 8px',
        'success_color' => '#008000',
        'success_size' => 14,
        'invalid_color' => '#ff0000',
        'invalid_size' => 12,
      ]
    ];

  }

  public function stats()
  {

    $stats = Local::get('stats');

    if (!isset($stats['currency'])) {
      $shop_json = json_decode(Shopify::request('shop'), true);
      $shop = $shop_json['shop'];

      $stats['currency'] = $shop['currency'];
      Local::put('stats', $stats);
    }

    Helpers::json([
      'products' => isset($stats['products']) ? $stats['products'] : 0,
      'emails' => isset($stats['emails']) ? $stats['emails'] : 0,
      'numbers' => isset($stats['numbers']) ? $stats['numbers'] : 0,
      'sales' => isset($stats['sales']) ? $stats['sales'] * 100 : 0,
      'sent' => isset($stats['sent']) ? $stats['sent'] : 0,
      'smssent' => isset($stats['smssent']) ? $stats['smssent'] : 0,
      'currency' => isset($stats['currency']) ? $stats['currency'] : 'USD',
    ]);

  }

  public function products()
  {

    $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
    $search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 'created_at';
    $order = isset($_REQUEST['order']) ? (int) $_REQUEST['order'] : -1;
    $limit = isset($_REQUEST['limit']) ? (int) $_REQUEST['limit'] : 21;

    if ($search != '') {
      $lookfor = ['$text' => ['$search' => $search]];
    } else {
      $lookfor = [];
    }

    $items = Helpers::db()->products->find(
      $lookfor, 
      [
        'projection' => [
          'emails' => 0,
          'numbers' => 0
        ],
        'limit' => $limit, 
        'skip' => 20 * ($page - 1),
        'sort' => [
          $sortby => $order
        ]
      ]
    );

    Helpers::json($items->toArray());

  }

  public function subscriptions()
  {

    $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
    $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'emails';
    $product = isset($_REQUEST['product']) ? $_REQUEST['product'] : false;

    $doc = Helpers::db()->products->findOne(['_id' => Helpers::mid($product)]);
    $serialized = json_decode(json_encode($doc->bsonSerialize()), true);

    $items = array_reverse($serialized[$type]);
    $cut = array_slice($items, $page * 21 - 21, $page * 21);

    Helpers::json($cut);
    
  }

  public function add($data)
  {

    if (isset($data['product_id']) && isset($data['variant_id']) && (isset($data['email']) || isset($data['phone']))) {

      if (isset($data['email'])) {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
          Helpers::error(400, 'Your email address is invalid', 'email_invalid');
        }
      }

      $product_json = json_decode(Shopify::request('products/' . $data['product_id']), true);
      $product = $product_json['product'];

      if ($data['variant_id'] == null) {

        $variant = reset($product['variants']);

      } else {

        foreach ($product['variants'] as $var) {
          
          if ($var['id'] == $data['variant_id']) {

            $variant = $var;

          }

        }

      }

      $doc = Helpers::db()->products->findOne(['inventory_id' => $variant['inventory_item_id']]);
      $stats = Local::get('stats');

      if ($doc) {

        $update = [];
        $email_count = 0;
        $number_count = 0;

        if (isset($data['email']) && $data['email'] != '') {

          $emails = $doc['emails']->bsonSerialize();
          $emails_addresses = array_map(function($row) {
            return $row->value;
          }, $emails);

          if (in_array($data['email'], $emails_addresses)) {
            Helpers::error(400, 'Email address was already used for this product', 'email_used');
          }

          $email_count++;

          $update['$push'] = [
            'emails' => [
              'value' => $data['email'],
              'country' => isset($_SERVER['HTTP_CF_IPCOUNTRY']) ? $_SERVER['HTTP_CF_IPCOUNTRY'] : '',
              'tags' => isset($data['tags']) && $data['tags'] ? $data['tags'] : '',
              'created_at' => date('Y-m-d\TH:i:s')
            ]
          ];

        }

        if (isset($data['phone']) && $data['phone'] != '') {

          $numbers = $doc['numbers']->bsonSerialize();
          $numbers_used = array_map(function($row) {
            return $row->value;
          }, $numbers);

          if (in_array($data['phone'], $numbers_used)) {
            Helpers::error(400, 'Phone number was already used for this product', 'number_used');
          }

          $number_count++;

          if (!isset($update['$push'])) {
            $update['$push'] = [];
          }

          $update['$push']['numbers'] = [
            'value' => $data['phone'],
            'country' => isset($_SERVER['HTTP_CF_IPCOUNTRY']) ? $_SERVER['HTTP_CF_IPCOUNTRY'] : '',
            'tags' => isset($data['tags']) && $data['tags'] ? $data['tags'] : '',
            'created_at' => date('Y-m-d\TH:i:s')
          ];

        }

        $update['$inc'] = [
          'total_emails' => $email_count,
          'total_numbers' => $number_count
        ];

        $update['$set'] = [
          'updated_at' => date('Y-m-d\TH:i:s')
        ];

        Helpers::db()->products->updateOne(['_id' => $doc->_id], $update);

      } else {

        if ($data['variant_id'] == null) {
          $variant_title = '';
        } else {
          if ($variant['title'] == 'Default Title') {
            $variant_title = '';
          } else {
            $variant_title = $variant['title'];
          }
        }

        $res = json_decode(Shopify::request('products/' . $data['product_id']), true);
        $image = '';

        if ($data['variant_id']) {
          $found = false;

          foreach ($res['product']['images'] as $img) {

            if (in_array($data['variant_id'], $img['variant_ids']) && !$found) {

              $image = $img['src'];
              $found = true;

            }

          }

          if (!$found && isset($res['product']['images'][0])) {

            $image = $res['product']['images'][0]['src'];

          }

        } else {

          if (isset($res['product']['images'][0])) {

            $image = $res['product']['images'][0]['src'];

          }

        }

        $item = [
          'inventory_id' => $variant['inventory_item_id'],
          'image' => $image,
          'product' => [
            'id' => $data['product_id'],
            'title' => $product['title']
          ],
          'variant' => [
            'id' => $data['variant_id'],
            'sku' => $variant['sku'],
            'title' => $variant_title
          ],
          'total_emails' => isset($data['email']) && $data['email'] != '' ? 1 : 0,
          'total_numbers' => isset($data['phone']) && $data['phone'] != '' ? 1 : 0,
          'emails' => isset($data['email']) && $data['email'] != '' ? [[
            'value' => $data['email'],
            'country' => isset($_SERVER['HTTP_CF_IPCOUNTRY']) ? $_SERVER['HTTP_CF_IPCOUNTRY'] : '',
            'tags' => isset($data['tags']) && $data['tags'] ? $data['tags'] : '',
            'created_at' => date('Y-m-d\TH:i:s')
          ]] : [],
          'numbers' => isset($data['phone']) && $data['phone'] != '' ? [[
            'value' => $data['phone'],
            'country' => isset($_SERVER['HTTP_CF_IPCOUNTRY']) ? $_SERVER['HTTP_CF_IPCOUNTRY'] : '',
            'tags' => isset($data['tags']) && $data['tags'] ? $data['tags'] : '',
            'created_at' => date('Y-m-d\TH:i:s')
          ]] : [],
          'created_at' => date('Y-m-d\TH:i:s'),
          'updated_at' => date('Y-m-d\TH:i:s')
        ];

        Helpers::db()->products->insertOne($item);

        $stats['products'] = isset($stats['products']) ? $stats['products'] + 1 : 1;

      }

      if (isset($data['email']) && $data['email'] != '') {
        $stats['emails'] = isset($stats['emails']) ? $stats['emails'] + 1 : 1;
      }

      if (isset($data['phone']) && $data['phone'] != '') {
        $stats['numbers'] = isset($stats['numbers']) ? $stats['numbers'] + 1 : 1;
      }

      Local::put('stats', $stats);

      $this->API('subscribe', $data);

      Helpers::success();

    } else {

      Helpers::error(400, 'One of the required fields is missing', 'field_missing');

    }

  }

  public function remove()
  {

    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['type'])) {

      switch ($data['type']) {

        case 'product':

          $doc = Helpers::db()->products->findOne(['_id' => Helpers::mid($data['_id'])]);

          Helpers::db()->products->deleteOne(['_id' => Helpers::mid($data['_id'])]);

          $stats = Local::get('stats');
          $stats['products'] = $stats['products'] - 1;

          if (isset($doc['total_emails']) && $doc['total_emails']) {
            $stats['emails'] =  $stats['emails'] - $doc['total_emails'];
          }

          if (isset($doc['total_numbers']) && $doc['total_numbers']) {
            $stats['numbers'] =  $stats['numbers'] - $doc['total_numbers'];
          }

          Local::put('stats', $stats);
          
          break;

        default:

          Helpers::db()->products->updateOne(['_id' => Helpers::mid($data['_id'])], [
            '$inc' => [
              'total_' . $data['type'] => -1
            ],
            '$pull' => [
              $data['type'] => ['value' => $data['value']]
            ]
          ]);

          $stats = Local::get('stats');
          $stats[$data['type']] = $stats[$data['type']] - 1;

          Local::put('stats', $stats);
          
          break;
      }

    } else {

      Helpers::error(400, 'Type is missing', 'type_missing');

    }

  }

  public function track($data)
  {

    if (isset($data['product_id']) && isset($data['variant_id']) && isset($data['order_id'])) {

      $item = [
        'product_id' => $data['product_id'],
        'variant_id' => $data['variant_id'],
        'order_id' => $data['order_id'],
        'total' => $data['total'],
        'created_at' => $data['created_at']
      ];

      Helpers::db()->sales->insertOne($item);

      $stats = Local::get('stats');

      if (isset($stats['sales'])) {
        $stats['sales'] += (float) $data['total'];
      } else {
        $stats['sales'] = (float) $data['total'];
      }

      Local::put('stats', $stats);
      Helpers::success();

    }
    
  }

  public function templates()
  {

    $items = Local::get('templates');
    $billing = Local::get('billing');

    if (isset($billing['plan'])) {

      if (isset($billing['status']) && $billing['status'] != 'active') {

        $items = array_slice($items, 0, 1);

      } else {

        if ($billing['plan'] == 'starter') {

          $items = array_slice($items, 0, 3);

        }

      }

    } else {

      $items = array_slice($items, 0, 1);

    }

    $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;

    $cut = array_slice($items, $page * 21 - 21, $page * 21);

    Helpers::json($cut);

  }

  public function template()
  {

    $items = Local::get('templates');
    $id = $_REQUEST['id'];

    $found = array_search($id, array_column($items, 'id'));

    $html = file_get_contents(__DIR__ . '/emails/notification.html');
    $data_products = json_decode(Shopify::request('products', ['limit' => 1]), true);
    $data_shop = json_decode(Shopify::request('shop'), true);

    $body = new Template();
    $body->parse($html);

    $preview = $body->render([
      'shop' => $data_shop['shop'],
      'product' => isset($data_products['products'][0]) ? $data_products['products'][0] : []
    ]);

    if (!is_bool($found)) {

      $items[$found]['preview'] = $preview;
      Helpers::json($items[$found]);

    } else {

      Helpers::json([
        'id' => $id,
        'active' => true,
        'title' => 'My Template',
        'conditions' => [],
        'from' => '',
        'subject' => '{{product.title | strip_html}} is now available to order from {{shop.name}}',
        'body' => $html,
        'preview' => $preview,
        'sms' => '{{product.title | strip_html}} is now available. Order: {{bitly}}',
        'sms_from' => 'Alert',
        'created_at' => date('Y-m-d\TH:i:s'),
        'updated_at' => date('Y-m-d\TH:i:s')
      ]);

    }

  }

  public function templatePreview()
  {

    $data = json_decode(file_get_contents('php://input'), true);
    $html = $data['html'];
    $data_products = json_decode(Shopify::request('products', ['limit' => 1]), true);
    $data_shop = json_decode(Shopify::request('shop'), true);

    $body = new Template();
    $body->parse($html);

    $preview = $body->render([
      'shop' => $data_shop['shop'],
      'product' => isset($data_products['products'][0]) ? $data_products['products'][0] : []
    ]);

    Helpers::json([
      'preview' => $preview
    ]);

  }

  public function templateSave()
  {

    $data = json_decode(file_get_contents('php://input'), true);
    unset($data['preview']);

    $items = Local::get('templates');
    $found = array_search($data['id'], array_column($items, 'id'));

    if (!is_bool($found)) {

      $items[$found] = $data;

    } else {

      $items[] = $data;

    }

    Local::put('templates', $items);
    Helpers::success();

  }

  public function templateRemove()
  {

    $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;

    if ($id) {

      $items = Local::get('templates');

      $found = array_search($id, array_column($items, 'id'));

      if (!is_bool($found)) {

        unset($items[$found]);

      } else {

        Helpers::error(404, 'Template not found');

      }

    } else {

      Helpers::error(404, 'Template not found');

    }

    Local::put('templates', $items);
    Helpers::success();

  }

  public function customization()
  {

    $customization = Local::get('customization');

    if ($customization) {

      Helpers::json($customization);

    } else {

      Helpers::json($this->defaultStyleConfig());

    }

  }

  public function customizationSave()
  {

    $data = json_decode(file_get_contents('php://input'), true);
    Local::put('customization', $data);
    Helpers::success();

  }

  public function integrations()
  {

    Helpers::json(Local::get('integrations'));

  }

  public function integrationsUpdate()
  {

    $data = json_decode(file_get_contents('php://input'), true);

    unset($data['data']['updating']);

    if ($data['data']['active']) {
      $validation = call_user_func(['Minion\Integrations', $data['integration']], 'validate', $data['data']);
      $validated = true;
    } else {
      $validation = true;
      $validated = false;
    }
    
    if ($validation) {

      $integrations = Local::get('integrations');

      $data['data']['valid'] = $validated;
      $integrations[$data['integration']] = $data['data'];

      Local::put('integrations', $integrations);
      Helpers::success();

    } else {

      Helpers::json([
        'validation' => 'failed'
      ]); 

    }

  }

  public function webhook()
  {

    $data = json_decode(file_get_contents('php://input'), true);

    if ($data['available'] > 0) {

      $doc = Helpers::db()->products->findOne(['inventory_id' => $data['inventory_item_id']]);

      if ($doc) {

        $item = json_decode(json_encode($doc->bsonSerialize()), true);
        $data_product = json_decode(Shopify::request('products/' . $item['product']['id']), true);

        if (isset($data_product['errors'])) {
          Helpers::success();
        }

        $data_shop = json_decode(Shopify::request('shop'), true);
        $templates = Local::get('templates');
        $mails = [];
        $smses = [];
        $billing = Local::get('billing');

        if (count($templates)) {

          if (isset($billing['plan'])) {

            if (isset($billing['status']) && $billing['status'] != 'active') {

              $templates = array_slice($templates, 0, 1);

            } else {

              if ($billing['plan'] == 'starter') {

                $templates = array_slice($templates, 0, 3);

              }

            }

          } else {

            $templates = array_slice($templates, 0, 1);

          }

          $rendered = [];
          $rendered_sms = [];

          foreach ($templates as $template) {

            if (!$template['active']) {
              continue;
            }

            $conditions = $template['conditions'];

            foreach ($item['emails'] as $key => $email) {
              $match = false;

              foreach ($conditions as $condition) {
                if ($condition['type'] == 'country') {
                  if (isset($email['country']) && $condition['value'] == $email['country']) {
                    $match = true;
                  }
                }
                if ($condition['type'] == 'tags') {
                  if (isset($email['tags']) && $email['tags'] && strpos($email['tags'], $condition['value']) !== false) {
                    $match = true;
                  }
                }
              }

              if ($match) {

                $found = array_search($template['id'], array_column($rendered, 'id'));

                if (is_bool($found)) {

                  $subject_tmpl = new Template();
                  $subject_tmpl->parse($template['subject']);

                  $subject = $subject_tmpl->render([
                    'shop' => $data_shop['shop'],
                    'product' => $data_product['product']
                  ]);

                  $tmpl = new Template();
                  $tmpl->parse($template['body']);
                  $body = $tmpl->render([
                    'shop' => $data_shop['shop'],
                    'product' => $data_product['product']
                  ]);

                  $rendered[] = [
                    'id' => $template['id'],
                    'from' => $template['from'],
                    'subject' => $subject,
                    'body' => $body
                  ];

                  $found = count($rendered) - 1;

                }

                $mails[] = [
                  'email' => $email['value'],
                  'name' => $data_shop['shop']['name'],
                  'from' => $rendered[$found]['from'],
                  'subject' => $rendered[$found]['subject'],
                  'body' => $rendered[$found]['body']
                ];

                unset($item['emails'][$key]);

              }
            }

            foreach ($item['numbers'] as $key => $number) {
              $match = false;

              foreach ($conditions as $condition) {
                if ($condition['type'] == 'country') {
                  if (isset($number['country']) && $condition['value'] == $number['country']) {
                    $match = true;
                  }
                }
                if ($condition['type'] == 'tags') {
                  if (isset($number['tags']) && $number['tags'] && strpos($number['tags'], $condition['value']) !== false) {
                    $match = true;
                  }
                }
              }

              if ($match) {

                $found = array_search($template['id'], array_column($rendered_sms, 'id'));

                if (is_bool($found)) {

                  $message = isset($template['sms']) && $template['sms'] != '' ? $template['sms'] : '{{product.title | strip_html}} is now available. Order: {{bitly}}';

                  $sms_tmpl = new Template();
                  $sms_tmpl->parse($message);

                  $long_url = 'https://' . $data_shop['shop']['domain'] . '/products/' . $data_product['product']['handle'];
                  $link = $this->API('shorten', $long_url);

                  $sms = $sms_tmpl->render([
                    'shop' => $data_shop['shop'],
                    'product' => $data_product['product'],
                    'bitly' => $link
                  ]);

                  $rendered_sms[] = [
                    'id' => $template['id'],
                    'message' => $sms
                  ];

                  $found = count($rendered_sms) - 1;

                }

                $smses[] = [
                  'number' => $number['value'],
                  'name' => isset($template['sms_from']) && $template['sms_from'] != '' ? $template['sms_from'] : 'Alert',
                  'message' => $rendered_sms[$found]['message']
                ];

                unset($item['numbers'][$key]);

              }
            }
          }
        }

        $item['emails'] = array_values($item['emails']);
        $item['numbers'] = array_values($item['numbers']);

        if (!count($templates) || count($item['emails']) || count($item['numbers'])) {

          $subject_tmpl = new Template();
          $subject_tmpl->parse('{{product.title | strip_html}} is now available to order from {{shop.name}}');

          $subject = $subject_tmpl->render([
            'shop' => $data_shop['shop'],
            'product' => $data_product['product']
          ]);

          $tmpl = new Template();
          $html = file_get_contents(__DIR__ . '/emails/notification.html');
          $tmpl->parse($html);
          $body = $tmpl->render([
            'shop' => $data_shop['shop'],
            'product' => $data_product['product']
          ]);

          foreach ($item['emails'] as $email) {
            $mails[] = [
              'email' => $email['value'],
              'subject' => $subject,
              'body' => $body,
              'name' => $data_shop['shop']['name']
            ];
          }

          $long_url = 'https://' . $data_shop['shop']['domain'] . '/products/' . $data_product['product']['handle'];
          $link = $this->API('shorten', $long_url);

          $message = '{{product.title | strip_html}} is now available. Order: {{bitly}}';
          $sms_tmpl = new Template();
          $sms_tmpl->parse($message);

          $sms = $sms_tmpl->render([
            'shop' => $data_shop['shop'],
            'product' => $data_product['product'],
            'bitly' => $link
          ]);

          foreach ($item['numbers'] as $number) {
            $smses[] = [
              'number' => $number['value'],
              'name' => 'Alert',
              'message' => $sms
            ];
          }

        }

        $limits = Local::get('limits');
        $limit_notifications = $limits['notifications']['value'];

        if (isset($billing['plan'])) {

          if (isset($billing['status']) && $billing['status'] != 'active') {

            $limit = 50 - $limit_notifications;

          } else {

            if ($billing['plan'] == 'starter') {

              $limit = 250 - $limit_notifications;

            }

            if ($billing['plan'] == 'pro') {

              $limit = 5000 - $limit_notifications;

            }

            if ($billing['plan'] == 'unlimited') {

              $limit = null;

            }

          }

        } else {

          $limit = 50 - $limit_notifications;

        }

        $remove_mails = [];
        $remove_smses = [];

        foreach ($mails as $mail) {
          if ($limit == null || $limit > 0) {
            Local::queue([
              'shop' => $_SESSION['shop'],
              'type' => 'email',
              'data' => $mail
            ]);

            if ($limit != null) {
              $limit--;
              $remove_mails[] = $mail['email'];
            }
          }
        }

        foreach ($smses as $sms) {
          if ($limit == null || $limit > 0) {
            $this->API('sms', $sms);

            if (isset($billing['plan']) && $billing['plan'] == 'pro') {
              if ($billing['status'] == 'active') {
                $limit--;
                $remove_smses[] = $mail['number'];
              }
            } else {
              $remove_smses[] = $mail['number'];
            }
          }
        }

        if ($limit != null) {

          $limit_mails_on = count($mails) > count($remove_mails);
          $limit_smses_on = count($smses) > count($remove_smses);

          $mails = array_intersect($mails, $remove_mails);
          $smses = array_intersect($smses, $remove_smses);
        }

        $stats = Local::get('stats');

        if (count($mails)) {
          Helpers::db()->emails->insertOne([
            'inventory_id' => $data['inventory_item_id'],
            'product' => $item['product'],
            'variant' => $item['variant'],
            'emails' => array_map(function($mail) {
              return $mail['email'];
            }, $mails),
            'created_at' => date('Y-m-d\TH:i:s')
          ]);

          $stats['sent'] = isset($stats['sent']) ? $stats['sent'] + count($mails) : count($mails);
          $stats['emails'] = $stats['emails'] - count($mails);
        }

        if (count($smses)) {
          Helpers::db()->smses->insertOne([
            'inventory_id' => $data['inventory_item_id'],
            'product' => $item['product'],
            'variant' => $item['variant'],
            'smses' => array_map(function($sms) {
              return $sms['number'];
            }, $smses),
            'created_at' => date('Y-m-d\TH:i:s')
          ]);

          $stats['smssent'] = isset($stats['smssent']) ? $stats['smssent'] + count($smses) : count($smses);
          $stats['numbers'] = $stats['numbers'] - count($smses);
        }

        if ($limit == null) {

          $delete = true;

        } else {

          if ($limit_mails_on || $limit_smses_on) {

            $delete = false;

          } else {

            $delete = true;

          }

        }

        if ($delete) {

          Helpers::db()->products->deleteOne(['_id' => $doc['_id']]);

          $stats['products'] = $stats['products'] - 1;

        } else {

          foreach ($item['emails'] as $key => $email) {
            if (in_array($email['value'], $remove_mails)) {
              unset($item['emails'][$key]);
              $item['total_emails'] = $item['total_emails'] - 1;
            }
          }

          foreach ($item['numbers'] as $key => $email) {
            if (in_array($email['value'], $remove_smses)) {
              unset($item['numbers'][$key]);
              $item['total_numbers'] = $item['total_numbers'] - 1;
            }
          }

          Helpers::db()->products->updateOne(
            ['_id' => $doc['_id']],
            ['$set' => $item]
          );

        }

        $limits['notifications']['value'] = count($mails) + count($smses);
        Local::put('limits', $limits);
        Local::put('stats', $stats);

      }

    }

    Helpers::success();
    
  }

  /*==============================================
  =            EXPORT DATA TO XLS/CSV            =
  ==============================================*/
  public function export()
  {

    $data = json_decode(file_get_contents('php://input'), true);

    switch ($data['data']) {
      case 'products':
        $products = Helpers::db()->products->find();
        $items = [];

        foreach ($products as $product) {

          if ($data['range'] != 'all') {
            $param = str_replace('date_', '', $data['range']);
            $product_date = new \DateTime($product[$param]);

            $fromDate = explode('T', $data['date']['from']);
            $toDate = explode('T', $data['date']['to']);

            $from = new \DateTime($fromDate[0] . '00:00:01');
            $to = new \DateTime($toDate[0] . '23:59:59');

            if ($product_date < $from || $product_date > $to) {
              continue;
            }
          }

          $items[] = [
            'Product ID' => $product['product']['id'],
            'Product Title' => $product['product']['title'],
            'Variant Title' => $product['variant']['title'],
            'Variant ID' => $product['variant']['id'],
            'SKU' => $product['variant']['sku'],
            'Total Saved Emails' => count($product['emails']),
            'Total Saved Phone No' => count($product['numbers']),
            'Created At' => $product['created_at'],
            'Updated At' => $product['updated_at'],
          ];
        }

        break;
      
      default:
        $products = Helpers::db()->products->find();
        $items = [];

        foreach ($products as $product) {
          if ($data['data'] == 'emails') {
            $title = 'Email';
          } else {
            $title = 'Phone No';
          }

          foreach ($product[$data['data']] as $item) {

            if ($data['range'] != 'all') {
              $item_date = new \DateTime($item['created_at']);

              $fromDate = explode('T', $data['date']['from']);
              $toDate = explode('T', $data['date']['to']);

              $from = new \DateTime($fromDate[0] . '00:00:01');
              $to = new \DateTime($toDate[0] . '23:59:59');

              if ($item_date < $from || $item_date > $to) {
                continue;
              }
            }

            $items[] = [
              $title => $item['value'],
              'Created At' => $item['created_at'],
              'Product ID' => $product['product']['id'],
              'Product Title' => $product['product']['title'],
              'Variant Title' => $product['variant']['title'],
              'Variant ID' => $product['variant']['id'],
              'SKU' => $product['variant']['sku'],
            ];
          }
        }
        break;
      }

      //--- REMOVE FILES OLDER THAN 10 MINUTES
      $files = glob(DIR_PUBLIC . 'export/'.'*');
      $now = time();

      foreach ($files as $file) {
        if (is_file($file)) {
          if ($now - filemtime($file) >= 60 * 10) {
            unlink($file);
          }
        }
      }
      //-------------------------------------

      //--- EXPORT FUNCTION
      $filename = 'export_' . date('YmdHms') . '.csv';

      if (!file_exists(DIR_PUBLIC . 'export')) {
        mkdir(DIR_PUBLIC . 'export', 0777, true);
      }

      $path = DIR_PUBLIC . 'export/' . $filename;

      file_put_contents($path, '');
      $out = fopen($path, 'w+');

      $flag = false;
      foreach($items as $row) {
        if(!$flag) {
          fputcsv($out, array_keys($row), ',', '"');
          $flag = true;
        }
        fputcsv($out, array_values($row), ',', '"');
      }

      fclose($out);

      if ($data['type'] == 'XLS') {

        $spreadsheet = new Spreadsheet();
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();

        $reader->setDelimiter(',');
        $reader->setEnclosure('"');
        $reader->setSheetIndex(0);

        $spreadsheet = $reader->load($path);
        $writer = new Xls($spreadsheet);
        $writer->save(str_replace('.csv', '.xls', $path));

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        Helpers::json(['file' => HOST . '/export/' . str_replace('.csv', '.xls', $filename)]);

      } else if ($data['type'] == 'CSV') {

        Helpers::json(['file' => HOST . '/export/' . $filename]);

      }
      //-------------------------------------

  }

  private function API($type = 'subscribe', $data)
  {

    $integrations = Local::get('integrations');

    switch ($type) {

      case 'shorten': 

        $long_url = $data;
        $url = "https://api-ssl.bitly.com/v4/shorten";

        $headers = array('Authorization' => 'Bearer ' . BITLY, 'Content-Type' => 'application/json');
        $response = \Requests::post($url, $headers, json_encode(['long_url' => $long_url]));
        $res = json_decode($response->body);

        if (isset($res->link)) {
          return $res->link;
        } else {
          return $long_url;
        }

        break;

      case 'subscribe':

        $billing = Local::get('billing');
        $allow = true;

        if (isset($billing['plan'])) {

          if (isset($billing['status']) && $billing['status'] != 'active') {

            $allow = false;

          }

        } else {

          $allow = false;

        }

        if (isset($data['email']) && $allow) {

          if (Integrations::check('mailchimp')) {
            Local::queue([
              'shop' => $_SESSION['shop'],
              'type' => 'mailchimp',
              'data' => [
                'email' => $data['email'],
                'apikey' => $integrations['mailchimp']['apikey'],
                'listId' => $integrations['mailchimp']['listId']
              ]
            ]);
          }

          if (Integrations::check('klaviyo')) {
            Local::queue([
              'shop' => $_SESSION['shop'],
              'type' => 'klaviyo',
              'data' => [
                'email' => $data['email'],
                'apikey' => $integrations['klaviyo']['apikey'],
                'listId' => $integrations['klaviyo']['listId']
              ]
            ]);
          }

        }
        
        break;

      case 'sms':

        $billing = Local::get('billing');
        $allow = true;

        if (isset($billing['plan'])) {

          if (isset($billing['status']) && $billing['status'] != 'active') {

            $allow = false;

          } else {

            if ($billing['plan'] == 'starter') {

              $allow = false;

            }

          }

        } else {

          $allow = false;

        }

        if (isset($data['number']) && $allow) {

          if (Integrations::check('nexmo')) {
            Local::queue([
              'shop' => $_SESSION['shop'],
              'type' => 'nexmo',
              'data' => [
                'to' => $data['number'],
                'from' => $data['name'],
                'text' => $data['message'],
                'apikey' => $integrations['nexmo']['apikey'],
                'secret' => $integrations['nexmo']['secret']
              ]
            ]);
          }

        }
        break;
    }

  }
  
}