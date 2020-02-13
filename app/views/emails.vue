<template>
  <div>
  	<div class="grid hspace-between-xs">

  		<div class="sidebar">

        <component is="inc-sidebar" />

      </div>

  		<div class="main">
  			<ul class="breadcrumb">
  				<li class="breadcrumb-item"><a @click.prevent="$root.view = 'index'"><i class='bx bx-home-alt'></i></a></li>
  				<li class="breadcrumb-item breadcrumb-item.active">Subscriptions</li>
  			</ul>

  			<div class="card">
  				<div class="card-header">
            <div class="grid vcenter-xs">
              <div class="col-8-xs">
                <h1 class="title">Subscriptions</h1>
              </div>
              <div class="col-16-xs">
                <div class="grid vcenter-xs hend-xs">
                  <div class="field" v-if="list.length">
                    <div class="grid vcenter-xs">
                      <label class="mr-3">Sort by:</label>
                      <select class="small" v-model="params" @change="filter">
                        <option value="&sortby=created_at&order=-1">⯆ Date</option>
                        <option value="&sortby=created_at&order=1">⯅ Date</option>
                        <option value="&sortby=updated_at&order=-1">⯆ Update date</option>
                        <option value="&sortby=updated_at&order=1">⯅ Update date</option>
                        <option value="&sortby=total_emails&order=-1">⯆ Emails</option>
                        <option value="&sortby=total_emails&order=1">⯅ Emails</option>
                        <option value="&sortby=total_numbers&order=-1">⯆ Numbers</option>
                        <option value="&sortby=total_numbers&order=1">⯅ Numbers</option>
                        <option value="&sortby=product.title&order=-1">⯆ Product Title</option>
                        <option value="&sortby=product.title&order=1">⯅ Product Title</option>
                      </select>
                      <i class='bx bx-chevron-down'></i>
                    </div>
                  </div>
                  <div class="field ml-5">
                    <div class="grid vcenter-xs">
                      <label class="mr-3">Search:</label>
                      <input placeholder="Type title, ID, SKU" class="small search" v-model="search" @keyup.enter="filter" />
                      <a @click="filter"><i class='bx bx-search-alt'></i></a>
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
  								<tr v-for="(el, index) in list.slice(0,20)">
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
                      <a class="text-danger remove" @click.prevent="removeProduct(el)">
                        <i class="bx bx-trash"></i>
                      </a>
  										<a :href="admin_link(el.product.id, 'products')" target="_blank" class="text-success">
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
          <div class="card-footer py-4 nobg">
            <nav>
              <ul class="pagination justify-content-center mb-0">
                <li class="page-item" :class="{ disabled: page == 1 || loading || updating }">
                  <a class="page-link" href="#" @click.prevent="prevPage">
                    <i class="bx bx-left-arrow-alt"></i>
                    <span class="sr-only">Previous</span>
                  </a>
                </li>
                <li class="page-item" :class="{ disabled: list.length < 21 || loading || updating }">
                  <a class="page-link" href="#" @click.prevent="nextPage">
                    <i class="bx bx-right-arrow-alt"></i>
                    <span class="sr-only">Next</span>
                  </a>
                </li>
              </ul>
            </nav>
          </div>
  			</div>

  			<a href="http://www.minionmade.com" target="_blank" class="mm-logo"><img src="/assets/img/logo.png"></a>
  		</div>
  	</div>

    <div><component is="inc-modal" :modal="modal" /></div>
  </div>
</template>

<script>
module.exports = {
  data: function() {
    return {
      loading: true,
      updating: false,
      list: [],
      page: 1,
      search: '',
      modal: { active: false, tab: 'emails' },
      params: ''
   }
  },
  methods: {
    load: function() {
      var self = this

      url = '/app/products?page=' + this.page + '&search=' + this.search + this.params
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
        self.loading = false
        self.updating = false
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
      this.page = 1
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
          if (self.list.length == 1 && self.page > 1) {
            self.page--
          }
          self.load()
        })
        .catch((error) => {
          alert(error)
          self.loading = false
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
    }
  },
  mounted: function() {
    this.load()
  }
}
</script>