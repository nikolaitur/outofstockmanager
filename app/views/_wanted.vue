<template>
  <div class="card">
    <div class="card-header">
      <div class="grid vcenter-xs">
        <div class="col-8-xs">
          <h1 class="title">Most wanted</h1>
        </div>
        <div class="col-16-xs">
          <div class="grid hend-xs">
            <div class="field" v-if="list.length">
              <div class="grid vcenter-xs">
                <label class="mr-3">Sort by:</label>
                <select class="small" v-model="params" @change="filter">
                  <option value="&sortby=total_emails&order=-1">Emails</option>
                  <option value="&sortby=total_numbers&order=-1">Numbers</option>
                </select>
                <i class='bx bx-chevron-down'></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card-body" :class="{ 'updating': updating }">
      <div class="table-responsive-sm">
        <table class="table align-items-center" v-if="list.length">
          <thead class="thead-light">
            <tr>
              <th class="w-30">Product</th>
              <th class="w-20">Variant</th>
              <th class="w-20">Total Saved Emails</th>
              <th class="w-20">Total Saved Phone No.</th>
              <th class="w-10"></th>
            </tr>
          </thead>
          <tbody class="list">
            <tr v-for="(el, index) in list.slice(0,5)">
              <th>
                <div class="grid align-items-center">
                  <div class="avatar rounded-circle mr-3">
                    <img :src="img_url(el.image)" v-if="el.image">
                  </div>
                  <div class="media-body">
                    <span class="name text-primary">
                      {{ el.product.title }}
                    </span>
                  </div>
                </div>
              </th>
              <td>
                {{ el.variant.title || '-' }}
              </td>
              <td>
                <a class="text-default" @click.prevent="openModal(el, 'emails')"><label class="badge badge-primary mr-1">{{ el.total_emails }}</label> {{ el.total_emails == 1 ? 'email' : 'emails' }}</a>
              </td>
              <td>
                <a class="text-default" @click.prevent="openModal(el, 'numbers')"><label class="badge badge-primary mr-1">{{ el.total_numbers }}</label> {{ el.total_numbers == 1 ? 'number' : 'numbers' }}</a>
              </td>
              <td class="text-right">
                <div class="info" :class="{ active: el.info }">
                  <strong>Created at: </strong> {{ date_format(el.created_at) }}<br/>
                  <strong>Updated at: </strong> {{ date_format(el.updated_at) }}
                </div>
                <a class="text-info" @mouseenter="showInfo(el)" @mouseleave="hideInfo(el)">
                  <i class="bx bx-info-circle"></i>
                </a>
                <a class="remove" @click.prevent="removeProduct(el)">
                  <i class="bx bx-trash"></i>
                </a>
                <a :href="admin_link(el.product.id, 'products')" target="_blank" class="text-dark">
                  <i class="bx bx-link-external"></i>
                </a>
              </td>
            </tr>
          </tbody>
        </table>
        <table class="table align-items-center border-bottom" v-else>
          <thead>
            <tr>
              <th>
                <span v-if="loading"><span class="spinner"></span> Loading... Please wait</span>
                <span v-else>There are no active alerts</span>
              </th>
            </tr>
          </thead>
        </table>
      </div>
    </div>

    <div><component is="inc-modal" :modal="modal" /></div>
  </div>
</template>

<script>
module.exports = {
  props: ['reload'],
  data: function() {
    return {
      loading: true,
      updating: false,
      list: [],
      modal: false,
      params: '&sortby=total_emails&order=-1'
   }
  },
  watch: {
    reload: function() {
      this.updating = true
      this.load()
    }
  },
  methods: {
    load: function() {
      var self = this

      url = '/app/products?page=1' + this.params
      params = {
        method: 'GET',
        headers: this.$root.fetchHeaders
      }

      fetch(url, params)
      .then(errorCheck)
      .then(function(res) {
        self.list = res
        self.loading = false
        self.updating = false
      })
      .catch((error) => {
        alert(error)
      })
    },
    openModal: function(el, tab) {
      this.modal = {
        active: true,
        tab: tab,
        el: el
      }
    },
    filter: function() {
      this.updating = true
      this.load()
    },
    showInfo: function(el) {
      Vue.set(el, 'info', true)
    },
    hideInfo: function(el) {
      Vue.set(el, 'info', false)
    },
    removeProduct: function(el) {
      var self = this
      this.$root.showConfirm(function() {
        self.updating = true

        url = '/app/remove'
        params = {
          method: 'POST',
          headers: self.$root.fetchHeaders,
          body: JSON.stringify({
            _id: el._id.$oid,
            type: 'product'
          })
        }

        fetch(url, params)
        .then(errorCheck)
        .then(function(res) {
          self.$parent.reload = uniqid()
        })
        .catch((error) => {
          alert(error)
        })
      })
    }
  },
  mounted: function() {
    this.load()
  }
}
</script>