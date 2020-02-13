<template>
  <div class="modal fade confirm" :class="{ show: modal.active }">
    <div class="modal-dialog modal-lg" v-if="modal.el">
      <div class="modal-content">
        <div class="modal-header">
          <h2 class="modal-titley">{{ modal.el.product.title }}</h2>
          <h5 class="modal-subtitle mt-1 text-light" v-if="modal.el.variant.title">{{ modal.el.variant.title }}</h5>
          <a class="close" @click.prevent="modal.active = false"><span>×</span></a>
        </div>
        <div class="modal-body pt-0" :class="{ 'updating': updating }">
          <div class="tabs">
            <div class="tabs-nav">
              <div class="grid">
                <div class="col-12-xs">
                  <a :class="{ active: modal.tab == 'emails' }" @click.prevent="modal.tab = 'emails'">Emails</a>
                </div>
                <div class="col-12-xs">
                  <a :class="{ active: modal.tab == 'numbers' }" @click.prevent="modal.tab = 'numbers'">Numbers</a>
                </div>
              </div>
            </div>
          </div>
          <div class="table-responsive-sm">
            <table class="table align-items-center" v-if="tabs[modal.tab].list.length">
              <thead class="thead-light">
                <tr>
                  <th class="w-40" v-if="modal.tab == 'emails'">Email</th>
                  <th class="w-40" v-else>Number</th>
                  <th class="w-20">Country</th>
                  <th class="w-30">Created at</th>
                  <th class="w-10"></th>
                </tr>
              </thead>
              <tbody class="list">
                <tr v-for="(el, index) in tabs[modal.tab].list.slice(0,20)">
                  <th>
                    <span class="name text-primary">
                      {{ el.value }}
                    </span>
                  </th>
                  <td>
                    <component is="inc-country" :condition="{ type: 'country', value: el.country }" />
                  </td>
                  <td>
                    {{ date_format(el.created_at) }}
                  </td>
                  <td class="text-right">
                    <a class="text-danger remove m-0" @click.prevent="removeItem(el, index)">
                      <i class="bx bx-trash"></i>
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
                    <span>There are no items</span>
                  </th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
        <div class="modal-footer hspace-between-xs">
          <nav>
            <ul class="pagination justify-content-center mb-0">
              <li class="page-item" :class="{ disabled: tabs[modal.tab].page == 1 || loading || updating }">
                <a class="page-link" href="#" @click.prevent="prevPage">
                  <i class="bx bx-left-arrow-alt"></i>
                  <span class="sr-only">Previous</span>
                </a>
              </li>
              <li class="page-item" :class="{ disabled: tabs[modal.tab].list.length < 21 || loading || updating }">
                <a class="page-link" href="#" @click.prevent="nextPage">
                  <i class="bx bx-right-arrow-alt"></i>
                  <span class="sr-only">Next</span>
                </a>
              </li>
            </ul>
          </nav>
          <a class="btn" @click.prevent="modal.active = false">Close</a> 
        </div>
      </div>
    </div>
    <div class="modal-backdrop" @click.prevent="modal.active = false"></div>
  </div>
</template>

<script>
module.exports = {
  props: ['modal'],
  watch: {
    modal: function(n, o) {
      if (o == false || !o.el || (o.el && n.el._id.$oid != o.el._id.$oid)) {
        this.loading = true
        this.resetPages()
        first = this.modal.tab == 'emails' ? 'emails' : 'numbers';
        second = this.modal.tab == 'emails' ? 'numbers' : 'emails';
        this.load(first)
        this.load(second)
      }
    }
  },
  data: function() {
    return {
      loading: true,
      updating: false,
      tabs: {
        emails: {
          list: [],
          page: 1
        },
        numbers: {
          list: [],
          page: 1
        }
      }
    }
  },
  methods: {
    load: function(tab = this.modal.tab) {
      var self = this

      url = '/app/subscriptions?page=' + this.tabs[this.modal.tab].page + '&type=' + tab + '&product=' + this.modal.el._id.$oid
      params = {
        method: 'GET',
        headers: this.$root.fetchHeaders
      }

      fetch(url, params)
      .then(errorCheck)
      .then(function(res) {
        self.tabs[tab].list = res
        self.loading = false
        self.updating = false
      })
      .catch((error) => {
        alert(error)
        self.loading = false
        self.updating = false
      })
    },
    removeItem: function(el, index) {
      var self = this

      this.$root.showConfirm(function() {
        self.updating = true

        index = self.$parent.list.indexOf(self.modal.el)

        url = '/app/remove'
        params = {
          method: 'POST',
          headers: self.$root.fetchHeaders,
          body: JSON.stringify({
            _id: self.modal.el._id.$oid,
            value: el.value,
            type: self.modal.tab
          })
        }

        fetch(url, params)
        .then(errorCheck)
        .then(function(res) {
          if (self.$parent.$vnode.tag.indexOf('view-emails') == -1) {
            self.$parent.$parent.reload = uniqid()
          } else {
            self.$parent.list[index]['total_' + self.modal.tab]--
          }

          self.load()
        })
        .catch((error) => {
          alert(error)
          self.updating = false
        })
      })
    },
    nextPage: function() {
      this.updating = true
      this.page++
      this.load()
    },
    prevPage: function() {
      this.updating = true
      this.page--
      this.load()
    },
    resetPages: function() {
      Vue.set(this, 'tabs', {
        emails: {
          list: [],
          page: 1
        },
        numbers: {
          list: [],
          page: 1
        }
      })
    }
  }
}
</script>