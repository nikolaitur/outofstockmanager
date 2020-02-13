<template>
  <div>
  	<div class="grid hspace-between-xs">

  		<div class="sidebar">

  			<component is="inc-sidebar" />

  		</div>

  		<div class="main">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a @click.prevent="$root.view = 'index'"><i class='bx bx-home-alt'></i></a></li>
          <li class="breadcrumb-item"><a @click.prevent="$root.view = 'templates'">Templates</a></li>
          <li class="breadcrumb-item breadcrumb-item.active">Edit</li>
        </ul>

        <div class="grid m--3" :class="{ updating: loading }">
          <div class="col-14-m">
            <div class="card m-3">
              <div class="card-header">
                <div class="grid vcenter-xs">
                  <div class="col-24-xs">
                    <h1 class="title">Template</h1>
                  </div>
                </div>
              </div>
              <div class="card-body" :class="{ updating: previewing }">
                <div class="tabs">
                  <div class="tabs-nav border-bottom">
                    <div class="grid">
                      <div class="col-8-xs">
                        <a :class="{ active: tab == 'preview' }" @click.prevent="tab = 'preview'">Preview</a>
                      </div>
                      <div class="col-8-xs">
                        <a :class="{ active: tab == 'html' }" @click.prevent="tab = 'html'">HTML Code</a>
                      </div>
                      <div class="col-8-xs">
                        <a :class="{ active: tab == 'sms' }" @click.prevent="tab = 'sms'">SMS</a>
                      </div>
                    </div>
                  </div>
                  <div class="tab pt-4" v-if="tab == 'preview'" v-html="item.preview"></div>
                  <div class="tab pt-4" v-if="tab == 'html'">
                    <div class="field">
                      <textarea v-model="item.body" class="w-100" style="min-height: 600px"></textarea>
                    </div>
                  </div>
                  <div class="tab pt-4" v-if="tab == 'sms'">
                    <div class="field">
                      <label>Sender (number or string, max 11 chars, no spaces)</label>
                      <input class="mt-2 w-100" v-model="item.sms_from">
                    </div>
                    <div class="field mt-3">
                      <label>Message</label>
                      <textarea v-model="item.sms" class="w-100 mt-2"></textarea>
                    </div>
                  </div>
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
                      <a class="btn btn-primary" @click.prevent="save">Save</a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body" >
                <div class="field d-inline-block">
                  <div class="grid vcenter-xs">
                    <label class="mr-3">Active:</label>
                    <select class="small" v-model="item.active">
                      <option :value="false">False</option>
                      <option :value="true">True</option>
                    </select>
                    <i class='bx bx-chevron-down'></i>
                  </div>
                </div>
                <div class="field mt-3">
                  <label>Template title</label>
                  <input class="mt-2 w-100" v-model="item.title">
                </div>
                <div class="field mt-3">
                  <label>Email from address</label>
                  <input class="mt-2 w-100" v-model="item.from">
                </div>
                <div class="field mt-3">
                  <label>Subject</label>
                  <textarea class="mt-2 w-100" v-model="item.subject"></textarea>
                </div>
              </div>
            </div>

            <div class="card m-3 conditions">
              <div class="card-header">
                <div class="grid vcenter-xs">
                  <div class="col-24-xs">
                    <div class="grid hspace-between-xs vcenter-xs">
                      <h1 class="title">Conditions</h1>
                      <a class="btn btn-small" @click.prevent="add">Add</a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body" >
                <div class="alert alert-info alert-small" v-if="!item.conditions.length">
                  <div class="grid vcenter-xs flex-nowrap">
                    <i class="bx bx-info-square icon"></i>
                    <div class="message">
                      If conditions of different templates overlap, first one that meets the criteria, will be used for emails.
                    </div>
                  </div>
                </div>

                <div v-if="item.conditions && item.conditions.length">
                  <div class="grid mt-2" v-for="(condition, index) in item.conditions">
                    <div class="col-12-xs">
                      <div class="field">
                        <div class="grid vcenter-xs">
                          <div class="col-5-xs text-center">
                            <label v-if="index == 0">If</label>
                            <label v-else>or If</label>
                          </div>
                          <div class="col-19-xs">
                            <select class="small w-100" v-model="condition.type">
                              <option value="country">Country</option>
                              <option value="customer">Customer Tag</option>
                            </select>
                            <i class='bx bx-chevron-down'></i>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-12-xs">
                      <div class="field" v-if="condition.type == 'country'">
                        <div class="grid vcenter-xs">
                          <div class="col-3-xs text-center">
                            <label>is</label>
                          </div>
                          <div class="col-21-xs">
                            <select class="small w-100" v-model="condition.value">
                              <option value="AF">Afghanistan</option><option value="AX">Åland Islands</option><option value="AL">Albania</option><option value="DZ">Algeria</option><option value="AS">American Samoa</option><option value="AD">Andorra</option><option value="AO">Angola</option><option value="AI">Anguilla</option><option value="AQ">Antarctica</option><option value="AG">Antigua and Barbuda</option><option value="AR">Argentina</option><option value="AM">Armenia</option><option value="AW">Aruba</option><option value="AU">Australia</option><option value="AT">Austria</option><option value="AZ">Azerbaijan</option><option value="BS">Bahamas</option><option value="BH">Bahrain</option><option value="BD">Bangladesh</option><option value="BB">Barbados</option><option value="BY">Belarus</option><option value="BE">Belgium</option><option value="BZ">Belize</option><option value="BJ">Benin</option><option value="BM">Bermuda</option><option value="BT">Bhutan</option><option value="BO">Bolivia, Plurinational State of</option><option value="BQ">Bonaire, Sint Eustatius and Saba</option><option value="BA">Bosnia and Herzegovina</option><option value="BW">Botswana</option><option value="BV">Bouvet Island</option><option value="BR">Brazil</option><option value="IO">British Indian Ocean Territory</option><option value="BN">Brunei Darussalam</option><option value="BG">Bulgaria</option><option value="BF">Burkina Faso</option><option value="BI">Burundi</option><option value="KH">Cambodia</option><option value="CM">Cameroon</option><option value="CA">Canada</option><option value="CV">Cape Verde</option><option value="KY">Cayman Islands</option><option value="CF">Central African Republic</option><option value="TD">Chad</option><option value="CL">Chile</option><option value="CN">China</option><option value="CX">Christmas Island</option><option value="CC">Cocos (Keeling) Islands</option><option value="CO">Colombia</option><option value="KM">Comoros</option><option value="CG">Congo</option><option value="CD">Congo, the Democratic Republic of the</option><option value="CK">Cook Islands</option><option value="CR">Costa Rica</option><option value="CI">Côte d'Ivoire</option><option value="HR">Croatia</option><option value="CU">Cuba</option><option value="CW">Curaçao</option><option value="CY">Cyprus</option><option value="CZ">Czech Republic</option><option value="DK">Denmark</option><option value="DJ">Djibouti</option><option value="DM">Dominica</option><option value="DO">Dominican Republic</option><option value="EC">Ecuador</option><option value="EG">Egypt</option><option value="SV">El Salvador</option><option value="GQ">Equatorial Guinea</option><option value="ER">Eritrea</option><option value="EE">Estonia</option><option value="ET">Ethiopia</option><option value="FK">Falkland Islands (Malvinas)</option><option value="FO">Faroe Islands</option><option value="FJ">Fiji</option><option value="FI">Finland</option><option value="FR">France</option><option value="GF">French Guiana</option><option value="PF">French Polynesia</option><option value="TF">French Southern Territories</option><option value="GA">Gabon</option><option value="GM">Gambia</option><option value="GE">Georgia</option><option value="DE">Germany</option><option value="GH">Ghana</option><option value="GI">Gibraltar</option><option value="GR">Greece</option><option value="GL">Greenland</option><option value="GD">Grenada</option><option value="GP">Guadeloupe</option><option value="GU">Guam</option><option value="GT">Guatemala</option><option value="GG">Guernsey</option><option value="GN">Guinea</option><option value="GW">Guinea-Bissau</option><option value="GY">Guyana</option><option value="HT">Haiti</option><option value="HM">Heard Island and McDonald Islands</option><option value="VA">Holy See (Vatican City State)</option><option value="HN">Honduras</option><option value="HK">Hong Kong</option><option value="HU">Hungary</option><option value="IS">Iceland</option><option value="IN">India</option><option value="ID">Indonesia</option><option value="IR">Iran, Islamic Republic of</option><option value="IQ">Iraq</option><option value="IE">Ireland</option><option value="IM">Isle of Man</option><option value="IL">Israel</option><option value="IT">Italy</option><option value="JM">Jamaica</option><option value="JP">Japan</option><option value="JE">Jersey</option><option value="JO">Jordan</option><option value="KZ">Kazakhstan</option><option value="KE">Kenya</option><option value="KI">Kiribati</option><option value="KP">Korea, Democratic People's Republic of</option><option value="KR">Korea, Republic of</option><option value="KW">Kuwait</option><option value="KG">Kyrgyzstan</option><option value="LA">Lao People's Democratic Republic</option><option value="LV">Latvia</option><option value="LB">Lebanon</option><option value="LS">Lesotho</option><option value="LR">Liberia</option><option value="LY">Libya</option><option value="LI">Liechtenstein</option><option value="LT">Lithuania</option><option value="LU">Luxembourg</option><option value="MO">Macao</option><option value="MK">Macedonia, the former Yugoslav Republic of</option><option value="MG">Madagascar</option><option value="MW">Malawi</option><option value="MY">Malaysia</option><option value="MV">Maldives</option><option value="ML">Mali</option><option value="MT">Malta</option><option value="MH">Marshall Islands</option><option value="MQ">Martinique</option><option value="MR">Mauritania</option><option value="MU">Mauritius</option><option value="YT">Mayotte</option><option value="MX">Mexico</option><option value="FM">Micronesia, Federated States of</option><option value="MD">Moldova, Republic of</option><option value="MC">Monaco</option><option value="MN">Mongolia</option><option value="ME">Montenegro</option><option value="MS">Montserrat</option><option value="MA">Morocco</option><option value="MZ">Mozambique</option><option value="MM">Myanmar</option><option value="NA">Namibia</option><option value="NR">Nauru</option><option value="NP">Nepal</option><option value="NL">Netherlands</option><option value="NC">New Caledonia</option><option value="NZ">New Zealand</option><option value="NI">Nicaragua</option><option value="NE">Niger</option><option value="NG">Nigeria</option><option value="NU">Niue</option><option value="NF">Norfolk Island</option><option value="MP">Northern Mariana Islands</option><option value="NO">Norway</option><option value="OM">Oman</option><option value="PK">Pakistan</option><option value="PW">Palau</option><option value="PS">Palestinian Territory, Occupied</option><option value="PA">Panama</option><option value="PG">Papua New Guinea</option><option value="PY">Paraguay</option><option value="PE">Peru</option><option value="PH">Philippines</option><option value="PN">Pitcairn</option><option value="PL">Poland</option><option value="PT">Portugal</option><option value="PR">Puerto Rico</option><option value="QA">Qatar</option><option value="RE">Réunion</option><option value="RO">Romania</option><option value="RU">Russian Federation</option><option value="RW">Rwanda</option><option value="BL">Saint Barthélemy</option><option value="SH">Saint Helena, Ascension and Tristan da Cunha</option><option value="KN">Saint Kitts and Nevis</option><option value="LC">Saint Lucia</option><option value="MF">Saint Martin (French part)</option><option value="PM">Saint Pierre and Miquelon</option><option value="VC">Saint Vincent and the Grenadines</option><option value="WS">Samoa</option><option value="SM">San Marino</option><option value="ST">Sao Tome and Principe</option><option value="SA">Saudi Arabia</option><option value="SN">Senegal</option><option value="RS">Serbia</option><option value="SC">Seychelles</option><option value="SL">Sierra Leone</option><option value="SG">Singapore</option><option value="SX">Sint Maarten (Dutch part)</option><option value="SK">Slovakia</option><option value="SI">Slovenia</option><option value="SB">Solomon Islands</option><option value="SO">Somalia</option><option value="ZA">South Africa</option><option value="GS">South Georgia and the South Sandwich Islands</option><option value="SS">South Sudan</option><option value="ES">Spain</option><option value="LK">Sri Lanka</option><option value="SD">Sudan</option><option value="SR">Suriname</option><option value="SJ">Svalbard and Jan Mayen</option><option value="SZ">Swaziland</option><option value="SE">Sweden</option><option value="CH">Switzerland</option><option value="SY">Syrian Arab Republic</option><option value="TW">Taiwan, Province of China</option><option value="TJ">Tajikistan</option><option value="TZ">Tanzania, United Republic of</option><option value="TH">Thailand</option><option value="TL">Timor-Leste</option><option value="TG">Togo</option><option value="TK">Tokelau</option><option value="TO">Tonga</option><option value="TT">Trinidad and Tobago</option><option value="TN">Tunisia</option><option value="TR">Turkey</option><option value="TM">Turkmenistan</option><option value="TC">Turks and Caicos Islands</option><option value="TV">Tuvalu</option><option value="UG">Uganda</option><option value="UA">Ukraine</option><option value="AE">United Arab Emirates</option><option value="GB">United Kingdom</option><option value="US">United States</option><option value="UM">United States Minor Outlying Islands</option><option value="UY">Uruguay</option><option value="UZ">Uzbekistan</option><option value="VU">Vanuatu</option><option value="VE">Venezuela, Bolivarian Republic of</option><option value="VN">Viet Nam</option><option value="VG">Virgin Islands, British</option><option value="VI">Virgin Islands, U.S.</option><option value="WF">Wallis and Futuna</option><option value="EH">Western Sahara</option><option value="YE">Yemen</option><option value="ZM">Zambia</option><option value="ZW">Zimbabwe</option>
                            </select>
                            <i class='bx bx-chevron-down'></i>
                          </div>
                        </div>
                        <a class="text-danger remove" @click.prevent="remove(index)"><i class='bx bx bx-trash'></i></a>
                      </div>
                      <div class="field" v-else>
                        <div class="grid vcenter-xs">
                          <div class="col-3-xs text-center">
                            <label>is</label>
                          </div>
                          <div class="col-21-xs">
                            <input v-model="condition.value" class="small w-100">
                          </div>
                        </div>
                        <a class="text-danger remove" @click.prevent="remove(index)"><i class='bx bx bx-trash'></i></a>
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
      item: this.$root.viewData,
      tab: 'preview'
   }
  },
  watch: {
    tab: function() {
      if (this.tab == 'preview') {
        var self = this

        this.previewing = true

        url = '/app/template/preview'
        params = {
          method: 'POST',
          headers: this.$root.fetchHeaders,
          body: JSON.stringify({ html: this.item.body })
        }

        fetch(url, params)
        .then(errorCheck)
        .then(function(res) {
          self.previewing = false
          self.item.preview = res.preview
        })
        .catch((error) => {
          alert(error)
          self.previewing = false
        })
      }
    }
  },
  methods: {
    save: function() {
      var self = this

      if (this.item.subject == '' || this.item.body == '' || this.item.title == '' || this.item.from == '') {
        self.$root.showToast('All setting fields are required', true)
        return
      } else if (this.item.from.indexOf('shopify.com') != -1) {
        self.$root.showToast('Do not use Shopify domains as your email', true)
        return
      } else {

        this.loading = true

        url = '/app/template/save'
        params = {
          method: 'POST',
          headers: this.$root.fetchHeaders,
          body: JSON.stringify(this.item)
        }

        fetch(url, params)
        .then(errorCheck)
        .then(function(res) {
          self.$root.showToast('Template saved')
          self.loading = false
        })
        .catch((error) => {
          alert(error)
          self.loading= false
        })

      }
    },
    add: function() {
      this.item.conditions.push({
        type: 'country',
        value: ''
      })
    },
    remove: function(index) {
      this.item.conditions.splice(index, 1)
    }
  },
  mounted: function() {
    var self = this

    url = '/app/template?id=' + this.item.id
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
  .conditions .remove { position: absolute; right: -20px; top: 50%; -webkit-transform: translateY(-50%); transform: translateY(-50%); }
</style>