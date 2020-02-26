<?php

$host = $data['host'];
$shop = $data['shop'];
$_token = $data['_token'];
$view = $data['view'];
$billing_plan = $data['billing_plan'];
$limit = $data['limit'];

?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://unpkg.com/boxicons@2.0.4/css/boxicons.min.css" rel='stylesheet'>
  <link rel="stylesheet" href="/assets/css/platform.css" />
  <script src="/assets/js/vue-dev.js"></script>
  <!-- <script src="/assets/js/vue.js"></script> -->
  <script src="/assets/js/vue-loader.js"></script>
  <script src="https://unpkg.com/v-calendar@1.0.1/lib/v-calendar.umd.min.js"></script>
  <script src="/assets/js/shopify.js"></script>
  <script src="/assets/js/jscolor.js"></script>

  <?php if(isset($data['assets'])) foreach ($data['assets'] as $asset) echo $asset; ?>

  <title>Minion Made</title>

  <script>

    if (window.top == window.self) {
      window.location.assign(`https://<?php echo $shop; ?>/admin/apps`);
    }

    window.shopURL = 'https://<?php echo $shop; ?>';
    window.xdomain = '<?php echo $shop; ?>';
    window.xtoken = '<?php echo $_token; ?>';
    window.initView = '<?php echo $view; ?>';
    window.billingPlan = <?php echo json_encode($billing_plan); ?>;
    window.limit = <?php echo json_encode($limit); ?>;

  </script>
</head>
<body>

  <div id="root">
    <component :is="'view-' + view"></component>
    <div class="toast" :class="{ active: toast, alert: toast.error }">
      {{ toast.message }}
    </div>
    
    <div class="modal fade confirm" :class="{ show: confirm.active }">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="modal-header">
            <a class="close" @click.prevent="confirm.active = false"><span>×</span></a>
          </div>
          <div class="modal-body">
            <div class="text-center">
              <i class="bx bx-bell"></i>
              <h2 class="m-2">{{ confirm.title }}</h2>
              <p v-if="confirm.subtitle">{{ confirm.subtitle }}</p>
            </div>
          </div>
          <div class="modal-footer hspace-between-xs">
              <a class="btn btn-primary" @click.prevent="runConfirm">{{ confirm.btn }}</a>
              <a class="btn" @click.prevent="confirm.active = false">Close</a> 
          </div>
        </div>
      </div>
      <div class="modal-backdrop" @click.prevent="confirm.active = false"></div>
    </div>

  </div>

  <script src="/assets/js/platform.js"></script>

</body>
</html>