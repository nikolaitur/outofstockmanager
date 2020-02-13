<?php

namespace Minion;

Class Billing
{

  public static function check()
  {

    $billing = Local::get('billing');

    if (isset($billing['plan'])) {

      $request = Shopify::request('recurring_application_charges/' . $billing['id']);
      $res = json_decode($request, true);

      $plan = $billing['plan'];
      $billing = $res['recurring_application_charge'];
      $billing['plan'] = $plan;

      Local::put('billing', $billing);

    }

  }
  
  public static function plan($plan = false)
  {

    $plans = json_decode(file_get_contents(DIR_APP . '/plans.json'), true);

    if ($plan == 'all') {
      return $plans;
    }

    if ($plan) {
      $found = array_search($plan, array_column($plans, 'value'));

      if (!is_bool($found)) {
        return $plans[$found];
      } else {
        return [];
      }
    }

    $billing = Local::get('billing');

    if (isset($billing['name'])) {

      $found = array_search($billing['name'], array_column($plans, 'label'));

      if (!is_bool($found)) {

        if (isset($billing['trial_ends_on']) && $billing['trial_ends_on'] && $billing['plan'] != null) {

          $now = time();
          $trial_end = strtotime($billing['trial_ends_on']);
          $datediff = $now - $trial_end;
          $trial = round($datediff / (60 * 60 * 24)) > 0 ? false : $billing['trial_ends_on'];

        } else {

          $trial = false;

        }

        return [
          'label' => $plans[$found]['label'],
          'value' => $plans[$found]['value'],
          'trial' => $trial,
          'status' => isset($billing['status']) ? $billing['status'] : 'pending'
        ];

      } else {

        return [
          'label' => 'Free',
          'value' => null,
          'trial' => false,
          'status' => 'active'
        ];

      }

    } else {

      return [
          'label' => 'Free',
          'value' => null,
          'trial' => false,
          'status' => 'active'
        ];

    }
    
  }

  public static function create($data)
  {

    $plan = self::plan($data['plan']);
    $trial = isset($plan['trial']) && $plan['trial'] != 0 ? $plan['trial'] : 0;

    $billing = Local::get('billing');
    if (isset($billing['trial_ends_on']) && $billing['trial_ends_on']) {

      $now = time();
      $trial_end = strtotime($billing['trial_ends_on']);
      $datediff = $now - $trial_end;
      $diff = round($datediff / (60 * 60 * 24));

      if ($diff < 0) {
        if ($trial) {
          $trial = -1 * $diff;
        }
      } else {
        $trial = 0;
      }

    }

    $recurring_application_charge = [
      'recurring_application_charge' => [
        'name' => $plan['label'],
        'price' => $plan['price'],
        'return_url' => 'https://' . $_SESSION['shop'] . '/admin/apps/' . API_KEY,
        'test' => ENV == 'dev',
        'trial_days' => $trial
      ]
    ];

    $request = Shopify::request('recurring_application_charges', $recurring_application_charge, 'POST');
    return json_decode($request, true);

  }

  public static function charge($data, $newplan)
  {

    $billing = Local::get('billing');
    $billing = array_merge($billing, [
      'id' => $data['id'],
      'status' => $data['status'],
      'upgrade' => $newplan
    ]);

    Local::put('billing', $billing);

    return $data['confirmation_url'];

  }

  public static function activate($charge_id)
  {

    $billing = Local::get('billing');

    if (isset($billing['id']) && $billing['id'] == $charge_id && $billing['status'] == 'accepted') {

      $recurring_application_charge = [
        'recurring_application_charge' => [
          'id' => $charge_id
        ]
      ];

      $request = Shopify::request('recurring_application_charges/' . $charge_id . '/activate', [], 'POST');
      $res = json_decode($request, true);

      $upgrade =  $billing['upgrade'];
      $billing = $res['recurring_application_charge'];
      $billing['plan'] = $upgrade;

      Local::put('billing', $billing);

    }

  }

  public function cancel()
  {

    $billing = Local::get('billing');
    $request = Shopify::request('recurring_application_charges/' . $billing['id'], [], 'DELETE');
    $request = Shopify::request('recurring_application_charges/' . $billing['id']);
    $res = json_decode($request, true);

    $billing = $res['recurring_application_charge'];
    $billing['name'] = 'Free';
    $billing['plan'] = null;

    Local::put('billing', $billing);
    
  }

}