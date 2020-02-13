<template>
  <div>
  	<div class="grid hspace-between-xs">

  		<div class="sidebar">

  			<component is="inc-sidebar" />

  		</div>

  		<div class="main">

        <div class="stats grid">
          <div class="col-12-m col-6-sm">
            <div class="stat" :class="{ 'counting' : counting }">
              <div class="spinner" v-show="counting"></div>
              <strong>{{ stats.products }}</strong>
              Product{{ stats.products == '1' ? '' : 's' }} in demand
            </div>
          </div>
          <div class="col-12-m col-6-sm">
            <div class="stat" :class="{ 'counting' : counting }">
              <div class="spinner" v-if="counting"></div>
              <strong>{{ stats.emails }} / {{ stats.numbers }}</strong>
              No of emails / phone no.
            </div>
          </div>
          <div class="col-12-m col-6-sm">
            <div class="stat" :class="{ 'counting' : counting }">
              <div class="spinner" v-if="counting"></div>
              <strong>{{ stats.sent }} / {{ stats.smssent }}</strong>
              Email{{ stats.sent == '1' ? '' : 's' }} / SMS{{ stats.smssent == '1' ? '' : 'es' }} sent
            </div>
          </div>
          <div class="col-12-m col-6-sm">
            <div class="stat" :class="{ 'counting' : counting }">
              <div class="spinner" v-if="counting"></div>
              <strong>{{ Shopify.formatMoney(stats.sales, stats.currency) }}</strong>
              Sales generated
            </div>  
          </div>
        </div>

        <div><component is="inc-wanted" :reload="reload" /></div>
        <div class="mt-5"><component is="inc-recent" :reload="reload" /></div>

  			<a href="http://www.minionmade.com" target="_blank" class="mm-logo"><img src="/assets/img/logo.png"></a>
  		</div>
  	</div>
  </div>
</template>

<script>
module.exports = {
  data: function() {
    return {
      counting: true,
      reload: false,
      stats: {
        'products': 0,
        'emails': 0,
        'numbers': 0,
        'sent': 0,
        'smssent': 0,
        'sales': 0
      }
   }
  },
  watch: {
    reload: function() {
      this.counting = true
      this.getStats()
    }
  },
  methods: {
    getStats: function() {
      var self = this

      url = '/app/stats'
      params = {
        method: 'GET',
        headers: this.$root.fetchHeaders
      }

      fetch(url, params)
      .then(errorCheck)
      .then(function(res) {
        if (res) {
          self.stats.products = res.products ? res.products : 0
          self.stats.emails = res.emails ? res.emails : 0
          self.stats.numbers = res.numbers ? res.numbers : 0
          self.stats.sent = res.sent ? res.sent : 0
          self.stats.sales = res.sales ? res.sales : 0
        }
        self.counting = false
      })
      .catch((error) => {
        alert(error)
      })
    }
  },
  mounted: function() {
    this.getStats()
  }
}
</script>