<template>
  <div>
  	<div class="grid hspace-between-xs">

  		<div class="sidebar">

  			<component is="inc-sidebar" />

  		</div>

  		<div class="main">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a @click.prevent="$root.view = 'index'"><i class='bx bx-home-alt'></i></a></li>
          <li class="breadcrumb-item breadcrumb-item.active">Billing</li>
        </ul>

        <div class="loading-skeleton mt-5 w-50" v-if="loading">
          <div></div><div></div><div></div>
        </div>

        <div class="pricing grid" v-else>
          <div class="col-6-xs" v-for="el in list">
            <div class="column selected" :class="{ updating: updating == el.value }">
              <h2>{{ el.label }}</h2>
              <strong class="price">{{ el.price_label }}</strong>
              <a class="btn btn-primary my-2 w-100" v-if="$root.billingPlan.value != el.value" @click.prevent="select(el)">Select this plan</a>
              <a class="btn my-2 w-100 disabled" v-else>Selected</a>
              <ul>
                <li v-for="feature in el.features" v-html="feature"></li>
              </ul>
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
      loading: true,
      updating: false,
      list: []
    }
  },
  methods: {
    load: function() {
      var self = this

      url = '/billing/plans'
      params = {
        method: 'GET',
        headers: this.$root.fetchHeaders
      }

      fetch(url, params)
      .then(errorCheck)
      .then(function(res) {
        self.list = res
        self.loading = false
      })
      .catch((error) => {
        alert(error)
        self.loading = false
      })
    },
    select: function(item) {
      var self = this

      this.updating = item.value

      if (item.value) {

        url = '/billing/upgrade'
        params = {
          method: 'POST',
          headers: this.$root.fetchHeaders,
          body: JSON.stringify({ 
            plan: item.value
          })
        }

      } else {

        url = '/billing/cancel'
        params = {
          method: 'POST',
          headers: this.$root.fetchHeaders
        }

      }
      

      fetch(url, params)
      .then(errorCheck)
      .then(function(res) {
        if (res.redirect) {
          top.window.location = res.redirect
        } else {
          self.updating = false

          if (res.plan) {
            Vue.set(self.$root, 'billingPlan', res.plan)
          }
        }
      })
      .catch((error) => {
        alert(error)
        self.updating = false
      })
    }
  },
  mounted: function() {
    this.load()
  }
}
</script>