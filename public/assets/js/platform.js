function dump(data) {
  console.log(JSON.parse(JSON.stringify(data)))
}
function errorCheck(res) {
  if (res.ok) {
    return res.json()
  } else {
    throw new Error(res.status + ' : ' + res.statusText);
  }      
}
function uniqid() {
  out = Math.random().toString(36).substr(2, 9)
  return ('id'+out);
}
function current_date() {
  d = new Date()
  return d.getFullYear() + "-" + ("0"+(d.getMonth()+1)).slice(-2) + "-" + ("0" + d.getDate()).slice(-2) + "T" + ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2) + ":" + ("0" + d.getSeconds()).slice(-2)
}

Vue.prototype.shopURL = window.shopURL
Vue.prototype.Shopify = Shopify
Vue.prototype.img_url = function(src, size = '100x100_crop_center') {
  split = src.split('.')
  split[split.length - 2] = split[split.length - 2] + '_' + size
  return split.join('.')
}
Vue.prototype.admin_link = function(id, handle) {
  if (handle.indexOf('collections') != -1) {
    handle = handle.split('_')[1]
  }
  return shopURL + '/admin/' + handle + '/' + id
}
Vue.prototype.date_format = function(date) {
  d = new Date(date)
  datestring = ("0" + d.getDate()).slice(-2) + "-" + ("0"+(d.getMonth()+1)).slice(-2) + "-" +
  d.getFullYear() + " || " + ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);
  return datestring
}
Vue.prototype.access = function(array = []) {
  plan = window.billingPlan
  arrayCheck = array.length ? array.indexOf(plan.value) != -1 : true

  return plan.value && plan.status == 'active' && arrayCheck
}

new Vue({
  el: '#root',
  data: {
    view: window.initView,
    viewData: null,
    viewPrevData: null,
    toast: false,
    confirm: { active: false },
    shopURL: shopURL,
    billingPlan: window.billingPlan,
    limits: window.limits,
    fetchHeaders: new Headers({
      'X-Shopify-Shop-Domain': window.xdomain,
      'X-Token': window.xtoken,
    })
  },
  watch: {
    view: function() {
      this.toast = false
    }
  },
  methods: {
    showToast: function(msg, error = false) {
      var self = this
      this.toast = {
        message: msg,
        error: error
      }
      setTimeout(function() {
        self.toast = false
      }, 3000)
    },
    showConfirm: function(callback, msg = '') {
      if (msg == '') {
        this.confirm = {
          active: true,
          title: 'Are you sure?',
          subtitle: 'This action can\'t be reversed',
          btn: 'Confirm'
        }
      } else {
        this.confirm = msg
      }
      this.confirm.callback = callback
    },
    runConfirm: function() {
      this.confirm.callback()
      this.confirm.active = false
    }
  }
})