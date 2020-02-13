<template>
  <div>
  	<div class="grid hspace-between-xs">

  		<div class="sidebar">

  			<component is="inc-sidebar" />

  		</div>

  		<div class="main">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a @click.prevent="$root.view = 'index'"><i class='bx bx-home-alt'></i></a></li>
          <li class="breadcrumb-item breadcrumb-item.active">Customization</li>
        </ul>

        <div class="grid m--3" :class="{ updating: loading }">
          <div class="col-14-m">
            <div class="card m-3">
              <div class="card-header">
                <div class="grid vcenter-xs">
                  <div class="col-24-xs">
                    <h1 class="title">Preview</h1>
                  </div>
                </div>
              </div>
              <div class="card-body" v-if="item">
                <div class="mm-out-of-stock-manager">

                  <div class="mm-out-of-stock-manager_heading" 
                    v-if="item.labels.title != ''"
                    :style="{ color: item.style.title_color, fontSize: item.style.title_size + 'px' }"
                  >{{ item.labels.title }}</div>

                  <div class="mm-out-of-stock-manager_desc" 
                    v-if="item.labels.desc != ''"
                    :style="{ color: item.style.desc_color, fontSize: item.style.desc_size + 'px' }"
                    >{{ item.labels.desc }}</div>

                  <div class="mm-out-of-stock-manager_field" v-if="item.email_active">

                    <div class="mm-out-of-stock-manager_label" 
                      v-if="item.labels.email != ''"
                       :style="{ color: item.style.label_color, fontSize: item.style.label_size + 'px' }"
                     >
                     <span class="mm-out-of-stock-manager_label_check checked" v-if="item.number_active"></span> 
                   {{ item.labels.email }}</div>

                    <input class="mm-out-of-stock-manager_input" :style="{ 'border-color': item.style.input_border_color }" />
                    <div class="mm-out-of-stock-manager_invalid">{{ item.labels.email_invalid }}</div>
                  </div>
                  <div class="mm-out-of-stock-manager_field"  v-if="item.number_active">

                    <div class="mm-out-of-stock-manager_label" 
                      v-if="item.labels.number != ''"
                     :style="{ color: item.style.label_color, fontSize: item.style.label_size + 'px' }"
                     >
                     <span class="mm-out-of-stock-manager_label_check checked" v-if="item.email_active"></span> 
                   {{ item.labels.number }}</div>

                    <div class="mm-out-of-stock-manager_grid">
                      <div class="mm-out-of-stock-manager_country-selector">
                        <div class="mm-out-of-stock-manager_country-selector_selected">
                          <div class="mm-out-of-stock-manager_country-selector_list_option"><img src="https://www.countryflags.io/us/flat/16.png"> (+1) <i class="mm-out-of-stock-manager_chevron"></i></div>
                        </div>
                        <div class="mm-out-of-stock-manager_country-selector_list">
                          <div class="mm-out-of-stock-manager_country-selector_list_option"><img src="https://www.countryflags.io/fr/flat/16.png"><span>(+33)</span> France</div>
                          <div class="mm-out-of-stock-manager_country-selector_list_option"><img src="https://www.countryflags.io/us/flat/16.png"><span>(+1)</span> United States</div>
                          <div class="mm-out-of-stock-manager_country-selector_list_option"><img src="https://www.countryflags.io/gb/flat/16.png"><span>(+44)</span> United Kingdom</div>
                        </div>
                      </div>
                      <input class="mm-out-of-stock-manager_input" :style="{ 'border-color': item.style.input_border_color }" />
                    </div>
                    <div class="mm-out-of-stock-manager_invalid">{{ item.labels.number_invalid }}</div>
                  </div>
                  <div class="mm-out-of-stock-manager_action">
                    <button class="mm-out-of-stock-manager_action_submit">{{ item.labels.submit }}</button>
                  </div>
                  <div class="mm-out-of-stock-manager_success">
                    {{ item.labels.success }}
                  </div>
                </div>
                <small class="mt-4 d-block" style="line-height:1.3"><em>* Final form can look slightly different based on your theme's stylesheet. <br/>** Country selector here is just a preview.</em></small>
              </div>
              <div class="card-body" v-else>
                <div class="loading-skeleton">
                  <div></div><div></div><div></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-10-m">
            <div class="card m-3">
              <div class="card-header">
                <div class="grid vcenter-xs">
                  <div class="col-24-xs">
                    <div class="grid hspace-between-xs v-center-xs">
                      <h1 class="title">Settings</h1>
                      <a class="btn btn-primary" @click.prevent="save" style="margin-top:-5px">Save</a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body">

                <div class="tabs">
                  <div class="tabs-nav border-bottom">
                    <div class="grid">
                      <div class="col-8-xs">
                        <a :class="{ active: tab == 'general' }" @click.prevent="tab = 'general'">General</a>
                      </div>
                      <div class="col-8-xs">
                        <a :class="{ active: tab == 'labels' }" @click.prevent="tab = 'labels'">Labels</a>
                      </div>
                      <div class="col-8-xs">
                        <a :class="{ active: tab == 'style' }" @click.prevent="tab = 'style'">Style</a>
                      </div>
                    </div>
                  </div>
                  <div class="tab pt-4" v-if="!item">
                    <div class="loading-skeleton">
                      <div></div><div></div><div></div>
                    </div>
                  </div>
                  <div class="tab pt-4" v-show="tab == 'general'" v-if="item">
                    <div class="field d-inline-block">
                      <div class="grid vcenter-xs">
                        <label class="mr-3">Show email field:</label>
                        <select class="small" v-model="item.email_active">
                          <option :value="false">False</option>
                          <option :value="true">True</option>
                        </select>
                        <i class='bx bx-chevron-down'></i>
                      </div>
                    </div>
                    <br/>
                    <div class="field d-inline-block mt-3">
                      <div class="grid vcenter-xs">
                        <label class="mr-3">Show phone field:</label>
                        <select class="small" v-model="item.number_active">
                          <option :value="false">False</option>
                          <option :value="true">True</option>
                        </select>
                        <i class='bx bx-chevron-down'></i>
                      </div>
                    </div>
                  </div>
                  <div class="tab pt-4" v-show="tab == 'labels'" v-if="item">
                    <div class="field">
                      <label>Title</label>
                      <input class="mt-2 w-100" v-model="item.labels.title">
                    </div>
                    <div class="field mt-3">
                      <label>Description</label>
                      <textarea class="mt-2 w-100" v-model="item.labels.desc"></textarea>
                    </div>
                    <div class="field mt-3">
                      <label>Email address label</label>
                      <input class="mt-2 w-100" v-model="item.labels.email">
                    </div>
                    <div class="field mt-3">
                      <label>Phone number label</label>
                      <input class="mt-2 w-100" v-model="item.labels.number">
                    </div>
                    <div class="field mt-3">
                      <label>Submit button</label>
                      <input class="mt-2 w-100" v-model="item.labels.submit">
                    </div>
                    <div class="field mt-3">
                      <label>Success message</label>
                      <input class="mt-2 w-100" v-model="item.labels.success">
                    </div>
                    <div class="field mt-3">
                      <label>Invalid email address</label>
                      <input class="mt-2 w-100" v-model="item.labels.email_invalid">
                    </div>
                    <div class="field mt-3">
                      <label>Invalid phone number</label>
                      <input class="mt-2 w-100" v-model="item.labels.number_invalid">
                    </div>
                    <div class="field mt-3">
                      <label>Email address exists</label>
                      <input class="mt-2 w-100" v-model="item.labels.email_used">
                    </div>
                    <div class="field mt-3">
                      <label>Phone number exists</label>
                      <input class="mt-2 w-100" v-model="item.labels.number_used">
                    </div>
                  </div>
                  <div class="tab pt-4" v-show="tab == 'style'" v-if="item">
                    <div class="grid">
                      <div class="col-11-xs">
                        <div class="field">
                          <label>Frame border color</label>
                          <component is="inc-colorpicker" :model="item.style.frame_border_color" index="frame_border_color" placeholder="None" />
                        </div>
                      </div>
                      <div class="col-2-xs"></div>
                      <div class="col-11-xs">
                        <div class="field">
                          <label>Frame padding (px)</label>
                          <input class="mt-2 w-100" v-model="item.style.frame_padding" @keypress="isNumber($event)">
                        </div>
                      </div>
                    </div>
                    <div class="grid mt-3">
                      <div class="col-11-xs">
                        <div class="field">
                          <label>Title color</label>
                          <component is="inc-colorpicker" :model="item.style.title_color" index="title_color" placeholder="Theme default" />
                        </div>
                      </div>
                      <div class="col-2-xs"></div>
                      <div class="col-11-xs">
                        <div class="field">
                          <label>Title font size (px)</label>
                          <input class="mt-2 w-100" v-model="item.style.title_size" @keypress="isNumber($event)">
                        </div>
                      </div>
                    </div>
                    <div class="grid mt-3">
                      <div class="col-11-xs">
                        <div class="field">
                          <label>Description color</label>
                          <component is="inc-colorpicker" :model="item.style.desc_color" index="desc_color" placeholder="Theme default" />
                        </div>
                      </div>
                      <div class="col-2-xs"></div>
                      <div class="col-11-xs">
                        <div class="field">
                          <label>Description font size (px)</label>
                          <input class="mt-2 w-100" v-model="item.style.desc_size" @keypress="isNumber($event)">
                        </div>
                      </div>
                    </div>
                    <div class="grid mt-3">
                      <div class="col-11-xs">
                        <div class="field">
                          <label>Label color</label>
                          <component is="inc-colorpicker" :model="item.style.label_color" index="label_color" placeholder="Theme default" />
                        </div>
                      </div>
                      <div class="col-2-xs"></div>
                      <div class="col-11-xs">
                        <div class="field">
                          <label>Label font size (px)</label>
                          <input class="mt-2 w-100" v-model="item.style.label_size" @keypress="isNumber($event)">
                        </div>
                      </div>
                    </div>
                    <div class="grid mt-3">
                      <div class="col-11-xs">
                        <div class="field">
                          <label>Inputs border color</label>
                          <component is="inc-colorpicker" :model="item.style.input_border_color" index="input_border_color" placeholder="Theme default" />
                        </div>
                      </div>
                      <div class="col-2-xs"></div>
                      <div class="col-11-xs">
                        <div class="field">
                          <label>Inputs font size (px)</label>
                          <input class="mt-2 w-100" v-model="item.style.input_size" @keypress="isNumber($event)">
                        </div>
                      </div>
                    </div>
                    <div class="grid mt-3">
                      <div class="col-11-xs">
                        <div class="field">
                          <label>Submit color</label>
                          <component is="inc-colorpicker" :model="item.style.submit_color" index="submit_color" placeholder="Theme default" />
                        </div>
                      </div>
                      <div class="col-2-xs"></div>
                      <div class="col-11-xs">
                        <div class="field">
                          <label>Submit background color</label>
                          <component is="inc-colorpicker" :model="item.style.submit_bg_color" index="submit_bg_color" placeholder="Theme default" />
                        </div>
                      </div>
                    </div>
                    <div class="grid mt-3">
                      <div class="col-11-xs">
                        <div class="field">
                          <label>Submit hover color</label>
                          <component is="inc-colorpicker" :model="item.style.submit_hover_color" index="submit_hover_color" placeholder="Theme default" />
                        </div>
                      </div>
                      <div class="col-2-xs"></div>
                      <div class="col-11-xs">
                        <div class="field">
                          <label>Submit hover bg color</label>
                          <component is="inc-colorpicker" :model="item.style.submit_hover_bg_color" index="submit_hover_bg_color" placeholder="Theme default" />
                        </div>
                      </div>
                    </div>
                    <div class="grid mt-3">
                      <div class="col-11-xs">
                        <div class="field">
                          <label>Submit font size (px)</label>
                          <input class="mt-2 w-100" v-model="item.style.submit_size" @keypress="isNumber($event)">
                        </div>
                      </div>
                      <div class="col-2-xs"></div>
                      <div class="col-11-xs">
                        <div class="field">
                          <label>Submit padding</label>
                          <input class="mt-2 w-100" v-model="item.style.submit_padding" placeholder="Theme default">
                        </div>
                      </div>
                    </div>
                    <div class="grid mt-3">
                      <div class="col-11-xs">
                        <div class="field">
                          <label>Success color</label>
                          <component is="inc-colorpicker" :model="item.style.success_color" index="success_color" placeholder="Theme default" />
                        </div>
                      </div>
                      <div class="col-2-xs"></div>
                      <div class="col-11-xs">
                        <div class="field">
                          <label>Success font size (px)</label>
                          <input class="mt-2 w-100" v-model="item.style.success_size" @keypress="isNumber($event)">
                        </div>
                      </div>
                    </div>
                    <div class="grid mt-3">
                      <div class="col-11-xs">
                        <div class="field">
                          <label>Error color</label>
                          <component is="inc-colorpicker" :model="item.style.invalid_color" index="invalid_color" placeholder="Theme default" />
                        </div>
                      </div>
                      <div class="col-2-xs"></div>
                      <div class="col-11-xs">
                        <div class="field">
                          <label>Error font size (px)</label>
                          <input class="mt-2 w-100" v-model="item.style.invalid_size" @keypress="isNumber($event)">
                        </div>
                      </div>
                    </div>
                  </div>
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
      previewing: false,
      item: null,
      tab: 'general'
   }
  },
  methods: {
    save: function() {
      var self = this

      this.loading = true

      url = '/app/customization/save'
      params = {
        method: 'POST',
        headers: this.$root.fetchHeaders,
        body: JSON.stringify(this.item)
      }

      fetch(url, params)
      .then(errorCheck)
      .then(function(res) {
        self.$root.showToast('Settings saved')
        self.loading = false
      })
      .catch((error) => {
        alert(error)
        self.loading= false
      })
    },
    isNumber: function(evt) {
      evt = (evt) ? evt : window.event;
      var charCode = (evt.which) ? evt.which : evt.keyCode;
      if ((charCode > 31 && (charCode < 48 || charCode > 57)) && charCode !== 46) {
        evt.preventDefault();
      } else {
        return true;
      }
    }
  },
  mounted: function() {
    var self = this

    url = '/app/customization'
    params = {
      method: 'GET',
      headers: this.$root.fetchHeaders
    }

    fetch(url, params)
    .then(errorCheck)
    .then(function(res) {
      self.loading = false
      self.item = res
    })
    .catch((error) => {
      alert(error)
      self.loading = false
    })
  }
}
</script>

<style>
.mm-out-of-stock-manager { padding: 24px; border: 1px solid #e9ecef; border-radius: 5px; }
.mm-out-of-stock-manager_heading { font-size: 20px; font-weight: bold; margin-bottom: 8px; }
.mm-out-of-stock-manager_desc { font-size: 16px; padding-bottom: 8px; }
.mm-out-of-stock-manager_field { margin-top: 16px; }
.mm-out-of-stock-manager_label { display: block; margin-bottom: 8px; text-transform: uppercase; font-size: 14px; letter-spacing: .5px; }
.mm-out-of-stock-manager_label_check { display: inline-block; cursor: pointer; vertical-align: middle; margin-top: -2px; margin-right: 8px; width: 17px; height: 7px; border-radius: 4px; background: #9A9999; position: relative; }
.mm-out-of-stock-manager_label_check::after { content: ''; position: absolute; width: 10px; height: 10px; border-radius: 50%; background :#fff; box-shadow: 0 3px 8px rgba(154, 153, 153, 0.5); top: -2px; left: -1px; }
.mm-out-of-stock-manager_label_check.checked { background: #b1dbb1; }
.mm-out-of-stock-manager_label_check.checked::after { background: green; left: calc(100% - 10px); box-shadow: 0 2px 9px rgba(109, 162, 108, 0.5); }
.mm-out-of-stock-manager_input { padding: 8px; width: 100%; border-radius: 3px; border: 1px solid #e9ecef; }
.mm-out-of-stock-manager_grid,
.mm-out-of-stock-manager_country-selector_list_option { position: relative; display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-flex-wrap:nowrap;-ms-flex-wrap:nowrap;flex-wrap:nowrap;-webkit-flex:0 1 auto;-ms-flex:0 1 auto;flex:0 1 auto;-webkit-flex-direction:row;-ms-flex-direction:row;flex-direction:row}
.mm-out-of-stock-manager_country-selector { width: 80px; border: 1px solid #e9ecef; }
.mm-out-of-stock-manager_country-selector_list { display: none; position: absolute; top: 100%; left: 0; width: 100%; background: #fff; border: 1px solid #e9ecef; border-top: 0; border-radius: 5px; border-top-left-radius: 0px; border-top-right-radius: 0px; }
.mm-out-of-stock-manager_country-selector + .mm-out-of-stock-manager_input { border-left: 0; border-bottom-left-radius: 0px; border-top-left-radius: 0px; }
.mm-out-of-stock-manager_country-selector_list_option { webkit-align-items:center;-ms-flex-align:center;align-items:center; }
.mm-out-of-stock-manager_country-selector_list_option img { margin-right: 8px; }
.mm-out-of-stock-manager_country-selector_list_option { padding: 8px; transition: background .2s ease; }
.mm-out-of-stock-manager_country-selector_list_option:hover { background: #eff3f7; cursor: pointer; }
.mm-out-of-stock-manager_country-selector_selected .mm-out-of-stock-manager_country-selector_list_option { background: none; }
.mm-out-of-stock-manager_country-selector_selected .mm-out-of-stock-manager_country-selector_list_option img { width: 16px; min-width: 16px; margin-bottom: -1px }
.mm-out-of-stock-manager_country-selector_list_option span { width: 50px; }
.mm-out-of-stock-manager_chevron { margin-left: 4px; width: 0;height: 0;border-style: solid;border-width: 4px 3px 0 3px;border-color: #000 transparent transparent transparent; }
.mm-out-of-stock-manager_country-selector:hover .mm-out-of-stock-manager_country-selector_list { display: block; }
.mm-out-of-stock-manager_action { margin-top: 16px; }
.mm-out-of-stock-manager_action_submit { width: 100%; display: block; background: #000; color: #fff; border: 0; border-radius: 3px; cursor: pointer; text-transform: uppercase; font-size: 14px; letter-spacing: .5px; font-weight: 700; padding: 16px 8px; transition: background .2s ease, color .2s ease; }
.mm-out-of-stock-manager_action_submit:hover { background: #4c4c4c; }
.mm-out-of-stock-manager_invalid { font-size: 12px; margin-top: 8px; color: red; }
.mm-out-of-stock-manager_success { margin-top: 16px; border: 1px solid green; padding: 16px 8px; color: green; text-align: center; }
</style>