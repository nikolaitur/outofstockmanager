<template>
  <div>
  	<div class="grid hspace-between-xs">

  		<div class="sidebar">

  			<component is="inc-sidebar" />

  		</div>

      <div class="main">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a @click.prevent="$root.view = 'index'"><i class='bx bx-home-alt'></i></a></li>
          <li class="breadcrumb-item breadcrumb-item.active">Export</li>
        </ul>

        <div class="card export">
          <div class="card-header">
            <div class="grid vcenter-xs">
              <div class="col-24-xs">
                <h1 class="title">Export</h1>
              </div>
            </div>
          </div>
          <div class="card-body pt-3" :class="{ 'updating': loading }" v-if="$root.billingPlan.value && $root.billingPlan.status == 'active'">
            <div class="field">
              <div class="grid vcenter-xs">
                <label class="mr-3 text-dark">Type:</label>
                <label class="radio">
                  <input type="radio" name="type" value="XLS" v-model="config.type">
                  <span>XLS</span>
                </label>
                <label class="radio ml-3">
                  <input type="radio" name="type" value="CSV" v-model="config.type">
                  <span>CSV</span>
                </label>
              </div>
            </div>
            <div class="field mt-5">
              <div class="grid vcenter-xs">
                <label class="mr-3 text-dark">Data:</label>
                <label class="radio">
                  <input type="radio" name="data" value="products" v-model="config.data">
                  <span>Products</span>
                </label>
                <label class="radio ml-3">
                  <input type="radio" name="data" value="emails" v-model="config.data">
                  <span>Emails</span>
                </label>
                <label class="radio ml-3">
                  <input type="radio" name="data" value="numbers" v-model="config.data">
                  <span>Numbers</span>
                </label>
              </div>
            </div>
            <div class="field mt-5">
              <div class="grid vcenter-xs">
                <label class="mr-3 text-dark">Range:</label>
                <label class="radio">
                  <input type="radio" name="range" value="all" v-model="config.range">
                  <span>All</span>
                </label>
                <label class="radio ml-3">
                  <input type="radio" name="range" value="date_created_at" v-model="config.range">
                  <span>Date of creation</span>
                </label>
                <label class="radio ml-3">
                  <input type="radio" name="range" value="date_updated_at" v-model="config.range">
                  <span>Date of update</span>
                </label>
              </div>
            </div>
            <div class="field mt-4" v-show="config.range == 'date_created_at' || config.range == 'date_updated_at'">
              <div class="grid vcenter-xs">
                <label class="mr-3 text-dark"></label>
                <div class="grid vcenter-xs date">
                  <label class="mr-3 default">From:</label>
                  <v-date-picker v-model="config.date.from" :input-props='{ class: "text-center" }' :max-date="config.date.to" />
                </div>
                <div class="grid vcenter-xs ml-3 date">
                  <label class="mr-3 default">To:</label>
                  <v-date-picker v-model="config.date.to" :input-props='{ class: "text-center" }' :min-date="config.date.from" />
                </div>
              </div>
            </div>
            <div class="mt-5 pb-2">
              <a class="btn btn-primary" @click.prevent="exportData">Export</a>
            </div>
          </div>
          <div class="card-body" v-else>

            <div class="alert alert-danger d-inline-block">
              <div class="grid vcenter-xs flex-nowrap">
                <i class='icon bx bx-x-circle'></i>
                <div class="message">
                  Export feature is not available in your plan. You can upgrade it <a @click.prevent="$root.view = 'billing'">here</a>.
                </div>
              </div>
            </div>

          </div>
        </div>

        <a href="http://www.minionmade.com" target="_blank" class="mm-logo"><img src="/assets/img/logo.png"></a>
      </div>

    </div>
  </div>
</template>

<script>
module.exports = {
  data: function() {
    return {
      loading: false,
      config: {
        type: 'XLS',
        data: 'products',
        range: 'all',
        date: {
          from: new Date(),
          to: new Date()
        }
      }
   }
  },
  methods: {
    exportData: function() {
      var self = this

      this.loading = true

      url = '/app/export'
      params = {
        method: 'POST',
        headers: this.$root.fetchHeaders,
        body: JSON.stringify(this.config)
      }

      fetch(url, params)
      .then(errorCheck)
      .then(function(res) {
        self.loading = false
        link = document.createElement('a')
        document.body.appendChild(link)
        link.target = '_blank'
        link.href = res.file
        link.click()
        document.body.removeChild(link)
      })
      .catch((error) => {
        alert(error)
        self.loading = false
      })
    }
  }
}
</script>

<style>
  .card.export .field label:not(.default) { text-transform: none; font-weight: 400; color: #212529; font-size: 14px; }
  .card.export .field label.mr-3:not(.default) { width: 64px; text-transform: uppercase; font-weight: 700; }
  .card.export .vc-popover-content-wrapper { opacity: 0; visibility: hidden }
  .card.export .field .date:hover .vc-popover-content-wrapper { opacity: 1; visibility: visible; }
</style>