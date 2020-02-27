<template>
  <div>
  	<div class="grid hspace-between-xs">

  		<div class="sidebar">

  			<component is="inc-sidebar" />

  		</div>

  		<div class="main">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a @click.prevent="$root.view = 'index'"><i class='bx bx-home-alt'></i></a></li>
          <li class="breadcrumb-item breadcrumb-item.active">Templates</li>
        </ul>

        <div class="alert alert-warning" v-if="!list.length">
          <div class="grid vcenter-xs flex-nowrap">
            <i class="bx bxs-error icon"></i>
            <div class="message">
              If you don't create any template, app will use the default one.
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="grid vcenter-xs">
              <div class="col-24-xs">
                <div class="grid vcenter-xs">
                  <h1 class="title mr-4">Templates</h1>
                  <a class="btn btn-small btn-primary" @click="select('new')">Add template</a>
                </div>
              </div>
            </div>
          </div>
          <div class="card-body" :class="{ 'updating': updating }">
            <div class="table-responsive-sm">
              <table class="table align-items-center" v-if="list.length">
                <thead class="thead-light">
                  <tr>
                    <th class="w-30">Title</th>
                    <th class="w-20">Email from</th>
                    <th class="w-40">Conditions</th>
                    <th class="w-10"></th>
                  </tr>
                </thead>
                <tbody class="list">
                  <tr v-for="(el, index) in list.slice(0,20)">
                    <th>
                      <a @click.prevent="select(el)"><span class="name">
                        <span style="font-size: 17px" class="mr-1" :class="{ 'text-danger' : !el.active, 'text-success' : el.active }">•</span> {{ el.title }}
                      </span></a>
                    </th>
                    <td>
                      {{ el.from }}
                    </td>
                    <td v-if="el.conditions.length">
                      <span v-for="(condition, index) in el.conditions">
                        <strong class="text-capitalize">{{ condition.type }}</strong> is <span><component is="inc-country" :condition="condition" /></span><span v-if="index != el.conditions.length - 1">, </span>
                      </span>
                    </td>
                    <td v-else>
                      To all customers
                    </td>
                    <td class="text-right">
                      <a class="text-danger remove" @click.prevent="remove(el.id)">
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
                      <span v-else>There are no templates</span>
                    </th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
          <div class="card-footer py-4 nobg">
            <nav>
              <ul class="pagination justify-content-center mb-0">
                <li class="page-item" :class="{ disabled: page == 1 || loading }">
                  <a class="page-link" href="#" @click.prevent="prevPage">
                    <i class="bx bx-left-arrow-alt"></i>
                    <span class="sr-only">Previous</span>
                  </a>
                </li>
                <li class="page-item" :class="{ disabled: list.length < 21 || loading }">
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
  </div>
</template>

<script>
module.exports = {
  data: function() {
    return {
      loading: true,
      updating: false,
      list: [],
      page: 1
   }
  },
  methods: {
    load: function() {
      var self = this

      url = '/app/templates?page=' + this.page
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
    select: function(item) {
      this.$root.viewPrevData = {
        page: this.page
      }

      if (item == 'new') {
        item = {
          id: uniqid(),
          conditions: []
        }

        if (!this.$root.billingPlan.value && this.list.length == 1) {
          this.$root.showToast('No more templates can be added. Upgrade your plan.', true)
          return;
        }

        if (this.access(['starter']) && this.list.length == 3) {
          this.$root.showToast('No more templates can be added. Upgrade your plan.', true)
          return;
        }
      }

      this.$root.viewData = item
      this.$root.view = 'template-edit'
    },
    remove: function(id) {
      var self = this

      this.$root.showConfirm(function() {
        self.updating = true

        url = '/app/template/remove?id=' + id
        params = {
          method: 'POST',
          headers: self.$root.fetchHeaders
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