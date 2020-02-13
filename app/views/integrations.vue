<template>
  <div>
  	<div class="grid hspace-between-xs">

  		<div class="sidebar">

  			<component is="inc-sidebar" />

  		</div>

  		<div class="main">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a @click.prevent="$root.view = 'index'"><i class='bx bx-home-alt'></i></a></li>
          <li class="breadcrumb-item breadcrumb-item.active">Integrations</li>
        </ul>

        <div class="grid m--3">
          <div class="col-12-m">
            <div class="card m-3">
              <div class="card-header">
                <div class="grid vcenter-xs">
                  <div class="col-24-xs">
                    <h1 class="title">Mailchimp</h1>
                    <h4 class="text-light">Audience subscription</h4>
                    <div class="logo"><component is="inc-logos-mailchimp" /></div>
                  </div>
                </div>
              </div>
              <div class="card-body" :class="{ updating: integrations.mailchimp.updating || loading }">
                <div class="field d-inline-block" :class="{ 'status-success' : integrations.mailchimp.active, 'status-danger' : !integrations.mailchimp.active }">
                  <div class="grid vcenter-xs">
                    <label class="mr-3">Active:</label>
                    <select class="small" v-model="integrations.mailchimp.active">
                      <option :value="false">False</option>
                      <option :value="true">True</option>
                    </select>
                    <i class='bx bx-chevron-down'></i>
                  </div>
                </div>
                <div class="field mt-3">
                  <label>API Key</label>
                  <input class="mt-2 w-100"  v-model="integrations.mailchimp.apikey">
                </div>
                <div class="field mt-3">
                  <label>Audience ID</label>
                  <input class="mt-2 w-100"  v-model="integrations.mailchimp.listId">
                </div>
                <div class="mt-3 position-absolute" v-if="integrations.mailchimp.active && !integrations.mailchimp.valid">
                  <div class="text-danger">Credentials are invalid - integration with Mailchimp will not work</div>
                </div>
                <div class="mt-5 pb-2">
                  <a class="btn btn-primary" @click.prevent="save('mailchimp')">Save</a>
                </div>
              </div>
            </div>
          </div>
          <div class="col-12-m">
            <div class="card m-3">
              <div class="card-header">
                <div class="grid vcenter-xs">
                  <div class="col-24-xs">
                    <h1 class="title">Klaviyo</h1>
                    <h4 class="text-light">List subscription</h4>
                    <div class="logo"><component is="inc-logos-klaviyo" /></div>
                  </div>
                </div>
              </div>
              <div class="card-body" :class="{ updating: integrations.klaviyo.updating || loading }">
                <div class="field d-inline-block" :class="{ 'status-success' : integrations.klaviyo.active, 'status-danger' : !integrations.klaviyo.active }">
                  <div class="grid vcenter-xs">
                    <label class="mr-3">Active:</label>
                    <select class="small" v-model="integrations.klaviyo.active">
                      <option :value="false">False</option>
                      <option :value="true">True</option>
                    </select>
                    <i class='bx bx-chevron-down'></i>
                  </div>
                </div>
                <div class="field mt-3">
                  <label>Private API Key</label>
                  <input class="mt-2 w-100"  v-model="integrations.klaviyo.apikey">
                </div>
                <div class="field mt-3">
                  <label>List ID</label>
                  <input class="mt-2 w-100"  v-model="integrations.klaviyo.listId">
                </div>
                <div class="mt-5 pb-2">
                  <a class="btn btn-primary" @click.prevent="save('klaviyo')">Save</a>
                </div>                
              </div>
            </div>
          </div>
          <div class="col-12-m">
            <div class="card m-3">
              <div class="card-header">
                <div class="grid vcenter-xs">
                  <div class="col-24-xs">
                    <h1 class="title">Nexmo</h1>
                    <h4 class="text-light">SMS Notifications - Global</h4>
                    <div class="logo"><component is="inc-logos-nexmo" /></div>
                  </div>
                </div>
              </div>
              <div class="card-body" :class="{ updating: integrations.nexmo.updating || loading }">
                <div class="field d-inline-block" :class="{ 'status-success' : integrations.nexmo.active, 'status-danger' : !integrations.nexmo.active }">
                  <div class="grid vcenter-xs">
                    <label class="mr-3">Active:</label>
                    <select class="small"v-model="integrations.nexmo.active">
                      <option :value="false">False</option>
                      <option :value="true">True</option>
                    </select>
                    <i class='bx bx-chevron-down'></i>
                  </div>
                </div>
                <div class="field mt-3">
                  <label>Key</label>
                  <input class="mt-2 w-100"  v-model="integrations.nexmo.apikey">
                </div>
                <div class="field mt-3">
                  <label>Secret</label>
                  <input class="mt-2 w-100"  v-model="integrations.nexmo.secret">
                </div>
                <div class="mt-5 pb-2">
                  <a class="btn btn-primary" @click.prevent="save('nexmo')">Save</a>
                </div>                
              </div>
            </div>
          </div>
          <div class="col-12-m">
            <div class="card m-3">
              <div class="card-header">
                <div class="grid vcenter-xs">
                  <div class="col-24-xs">
                    <h1 class="title">Twilio</h1>
                    <h4 class="text-light">SMS Notifications - US only</h4>
                    <div class="logo"><component is="inc-logos-twilio" /></div>
                  </div>
                </div>
              </div>
              <div class="card-body" :class="{ updating: integrations.twilio.updating || loading }">
                <div class="field d-inline-block" :class="{ 'status-success' : integrations.twilio.active, 'status-danger' : !integrations.twilio.active }">
                  <div class="grid vcenter-xs">
                    <label class="mr-3">Active:</label>
                    <select class="small"v-model="integrations.twilio.active">
                      <option :value="false">False</option>
                      <option :value="true">True</option>
                    </select>
                    <i class='bx bx-chevron-down'></i>
                  </div>
                </div>
                <div class="field mt-3">
                  <label>Account SID</label>
                  <input class="mt-2 w-100"  v-model="integrations.twilio.apikey">
                </div>
                <div class="field mt-3">
                  <label>Auth token</label>
                  <input class="mt-2 w-100"  v-model="integrations.twilio.secret">
                </div>
                <div class="mt-5 pb-2">
                  <a class="btn btn-primary" @click.prevent="save('twilio')">Save</a>
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
      loading: true,
      integrations: {
        mailchimp: {
          updating: false,
          active: false,
          apikey: '',
          listId: ''
        },
        klaviyo: {
          updating: false,
          active: false,
          apikey: '',
          listId: ''
        },
        nexmo: {
          updating: false,
          active: false,
          apikey: '',
          secret: ''
        },
        twilio: {
          updating: false,
          active: false,
          apikey: '',
          secret: ''
        }
      }
   }
  },
  methods: {
    getIntegrations: function() {
      var self = this

      url = '/app/integrations'
      params = {
        method: 'GET',
        headers: this.$root.fetchHeaders
      }

      fetch(url, params)
      .then(errorCheck)
      .then(function(res) {
        if (res) {
          Object.keys(self.integrations).forEach(function(key) {
            if (res[key]) {
              res[key].updating = false
              Vue.set(self.integrations, key, res[key])
            }
          })
        }
        self.loading = false
      })
      .catch((error) => {
        alert(error)
      })
    },
    save: function(card) {
      var self = this

      if (this.integrations[card].active == true) {
        if (this.integrations[card].apikey == '' || this.integrations[card].listId == '') {
          this.$root.showToast('All fields are required', true)
          return
        }
      }

      this.integrations[card].updating = true

      url = '/app/integrations/update'
      params = {
        method: 'POST',
        headers: this.$root.fetchHeaders,
        body: JSON.stringify({
          integration: card,
          data: this.integrations[card]
        })
      }

      fetch(url, params)
      .then(errorCheck)
      .then(function(res) {
        if (res.validation && res.validation == 'failed') {
          self.$root.showToast('Credentials are invalid', true)
          Vue.set(self.integrations[card], 'valid', false)
        } else {
          self.$root.showToast('Settings saved')
          Vue.set(self.integrations[card], 'valid', true)
        }

        self.integrations[card].updating = false
      })
      .catch((error) => {
        alert(error)
        self.integrations[card].updating = false
      })
    }
  },
  mounted: function() {
    this.getIntegrations()
  }
}
</script>