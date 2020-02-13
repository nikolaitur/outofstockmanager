<template>
  <div>
    <div class="app-info">
      <div class="icon"></div>
      <div class="title">Out of Stock Manager</div>
      <div class="plan">Your current plan: <strong>{{ $root.billingPlan.label }}</strong> <a class="d-inline-block align-middle" @click.prevent="$root.view = 'billing'"><i class='ml-1 bx bx-cog'></i></a>
        <div class="mt-2" v-if="$root.billingPlan.trial">Trial ends on: <strong>{{ $root.billingPlan.trial }}</strong></div>
      </div>
    </div>

    <div class="heading">Menu</div>

    <ul>
      <li>
        <a :class="{ 'active' : $root.view == 'index' }" @click.prevent="$root.view = 'index'"><i class='bx bx-tachometer'></i> Dashboard</a>
      </li>
      <li>
        <a :class="{ 'active' : $root.view == 'emails' }" @click.prevent="$root.view = 'emails'"><i class='bx bx-paper-plane'></i> Subscriptions</a>
      </li>
      <li>
        <a :class="{ 'active' : $root.view == 'export' }" @click.prevent="$root.view = 'export'"><i class='bx bx-export'></i> Export</a>
      </li>
    </ul>

    <div class="heading">Settings</div>

    <ul>
      <li>
        <a :class="{ 'active' : $root.view == 'templates' || $root.view == 'template-edit' }" @click.prevent="$root.view = 'templates'"><i class='bx bx-mail-send'></i> Templates</a>
      </li>
      <li>
        <a :class="{ 'active' : $root.view == 'customization' }" @click.prevent="$root.view = 'customization'"><i class='bx bxs-magic-wand'></i> Customization</a>
      </li>
      <li>
        <a :class="{ 'active' : $root.view == 'integrations' }" @click.prevent="$root.view = 'integrations'"><i class='bx bxl-mailchimp'></i> Integrations</a>
      </li>
    </ul>

    <div class="heading">Support</div>

    <ul>
      <li>
        <a :class="{ 'active' : $root.view == 'install' }" @click.prevent="$root.view = 'install'"><i class='bx bx-code-alt'></i> Installation</a>
      </li>
      <li>
        <a :class="{ 'active' : $root.view == 'help' }" @click.prevent="$root.view = 'help'"><i class='bx bx-help-circle'></i> Help</a>
      </li>
    </ul>
  </div>
</template>

<script>
module.exports = {}
</script>