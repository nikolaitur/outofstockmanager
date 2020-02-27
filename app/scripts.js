// Out of Stock Manager from Minion Made
// Contact: support@minionmade.com
/*

You can't mix forms with URL watchers

<div class="mm-out-of-stock-manager_wrapper" data-id="formId"></div>

<div class="mm-out-of-stock-manager_wrapper" data-id="URL" data-first="{{ product.selected_or_first_available_variant.id }}"></div>

MMOOSM.trigger('show', productId, variantId, domEl)
MMOOSM.trigger('hide', domEl)

*/

(function() {

	class MMOOSM {
		constructor(config) {
			this.handle = 'mm-out-of-stock-manager';
			this.config = config;
			this.installed = false;
			this.form = '';
			this.countries = [];
			this.watcher = {
				listeners: [],
				doc: window.document,
				MutationObserver: window.MutationObserver || window.WebKitMutationObserver,
				observer: null
			},
			this.instances = [];
		}

		init() {
			var self = this;

			if (window.Shopify.checkout) {
				this.checkout();
			}

			this.ready('form[action="/cart/add"]', function(el) {
				var id = self.instances.length;
				var path = window.location.href.replace(window.location.search, '');

				if (el.getAttribute('data-product-handle') && el.getAttribute('data-product-handle') != '') {
					path = '//' + window.location.hostname + '/products/' + el.getAttribute('data-product-handle');
				}

				self.instances[id] = {
					id: id,
					form: el,
					select: el.querySelector('[name="id"]'),
					productId: null,
					variantId: null,
					variants: []
				};

				self.getProductData(id, path);

				if (!self.installed) {
					self.injectCSS();
					self.form = self.generateDOM(self.dom());
					self.watch();
					self.manageForm();
					self.installed = true;
				}
			});

			if (window.meta.page.pageType == 'product') {
				var preBasedonUrl = document.querySelector('.mm-out-of-stock-manager_wrapper[data-id="URL"]');

				if (preBasedonUrl && !this.instances.length) {
					var path = window.location.href.replace(window.location.search, '');
					preBasedonUrl.setAttribute('data-instance-id', 0);
					self.instances.push({
						id: 0,
						productId: null,
						variantId: null,
						variants: [],
						watch: 'url'
					});

					self.getProductData(0, path, true);

					if (!self.installed) {
						self.injectCSS();
						self.form = self.generateDOM(self.dom());
						self.watchUrl();
						self.manageForm();
						self.installed = true;
					}
				}
			}
		}

		getProductData(id, path, urlBased = false) {
			var self = this;
			if (path.indexOf('/products') == -1) {
				return;
			}
			this.request('GET', path + '.js', function(err, data) {
				if (data) {
					var json = JSON.parse(data);
					if (!json.id) {
						self.instances.splice(id, 1);
						return;
					}
					self.instances[id].productId = json.id;
					json.variants.forEach(function(variant) {
						if (!variant.available) {
							self.instances[id].variants.push(variant.id);
						}
					});

					if (!urlBased) {
						var pre = document.querySelectorAll('.mm-out-of-stock-manager_wrapper[data-id="' + self.instances[id].form.getAttribute('id') + '"]');
						if (pre.length) {
							pre.forEach(function(p) {
								p.setAttribute('data-instance-id', id);
							});
						}
					}
					if (json.variants.length == self.instances[id].variants.length) {
						self.showDOM(self.instances[id]);
					} else {
						if (!urlBased) {
							self.run();
						} else {
							var param = document.querySelector('.mm-out-of-stock-manager_wrapper[data-id="URL"]');
							if (param && param.getAttribute('data-first') && param.getAttribute('data-first') != '') {
								if (self.instances[0].variants.indexOf(parseInt(param.getAttribute('data-first'))) != -1) {
									self.instances[0].variantId = parseInt(param.getAttribute('data-first'));
									self.showDOM(self.instances[0]);
								} else {
									self.hideDOM(self.instances[0]);
								}
							}
						}
					}
				}
			});
		}

		css(style, param, suffix = '') {
			return this.config.style[param] ? style + ' ' + this.config.style[param] + suffix + ';' : '';
		}

		injectCSS() {
			var t = [
			{ class: 'wrapper', val: 'display:none;' },
			{ class: '', val: `border-radius: 5px; ${this.css('border: 1px solid', 'frame_border_color')} ${this.css('padding:','frame_padding','px')}` },
			{ class: 'heading', val: `font-weight: bold; margin-bottom: 8px; ${this.css('color:', 'title_color')} ${this.css('font-size:', 'title_size', 'px')}` },
			{ class: 'desc', val: `padding-bottom: 8px; ${this.css('color:', 'desc_color')} ${this.css('font-size:', 'desc_size', 'px')}` },
			{ class: 'field', val: `margin-top: 16px;` },
			{ class: 'field .hide-el', val: `display: none;` },
			{ class: 'field .hide-el ~ *', val: `display: none!important;` },
			{ class: 'label', val: `display: block; margin-bottom: 8px; text-transform: uppercase; letter-spacing: .5px; ${this.css('color:', 'label_color')} ${this.css('font-size:', 'label_size', 'px')}` },
			{ class: 'label_check', val: 'display: inline-block; cursor: pointer; vertical-align: middle; margin-top: -2px; margin-right: 8px; width: 17px; height: 7px; border-radius: 4px; background: #9A9999; position: relative;' },
			{ class: 'label_check::after', val: 'content: ""; position: absolute; width: 10px; height: 10px; border-radius: 50%; background :#fff; box-shadow: 0 3px 8px rgba(154, 153, 153, 0.5); top: -2px; left: -1px;' },
			{ class: 'label_check.checked', val: 'background: #b1dbb1;' },
			{ class: 'label_check.checked::after', val: 'background: green; left: calc(100% - 10px); box-shadow: 0 2px 9px rgba(109, 162, 108, 0.5);' },
			{ class: 'input', val: `padding: 8px; width: 100%; border-radius: 3px; ${this.css('border: 1px solid', 'input_border_color','px')}` },
			{ class: 'grid', val: `position: relative; display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-flex-wrap:nowrap;-ms-flex-wrap:nowrap;flex-wrap:nowrap;-webkit-flex:0 1 auto;-ms-flex:0 1 auto;flex:0 1 auto;-webkit-flex-direction:row;-ms-flex-direction:row;flex-direction:row` },
			{ class: 'country-selector_list_option', val: `position: relative; display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-flex-wrap:nowrap;-ms-flex-wrap:nowrap;flex-wrap:nowrap;-webkit-flex:0 1 auto;-ms-flex:0 1 auto;flex:0 1 auto;-webkit-flex-direction:row;-ms-flex-direction:row;flex-direction:row` },
			{ class: 'country-selector', val: `${this.css('border: 1px solid', 'input_border_color')}` },
			{ class: 'country-selector_list', val: `display: none; 0px 0px 10px 1px rgba(0,0,0,.1); max-height: 300px; overflow: auto; position: absolute; top: 100%; left: 0; width: 100%; background: #fff; border: 1px solid #e9ecef; border-top: 0; border-radius: 5px; border-top-left-radius: 0px; border-top-right-radius: 0px;` },
			{ class: `country-selector + .mm-out-of-stock-manager_input`, val: `border-left: 0; border-bottom-left-radius: 0px; border-top-left-radius: 0px;` },
			{ class: 'country-selector_list_option', val: `webkit-align-items:center;-ms-flex-align:center;align-items:center;` },
			{ class: 'country-selector_list_option img', val: ` margin-right: 8px; width: 16px;` },
			{ class: 'country-selector_list_option', val: `padding: 8px; transition: background .2s ease;` },
			{ class: 'country-selector_list_option:hover', val: `background: #eff3f7; cursor: pointer;` },
			{ class: `country-selector_selected .mm-out-of-stock-manager_country-selector_list_option`, val: `background: none!important;` },
			{ class: `country-selector_selected .mm-out-of-stock-manager_country-selector_list_option span`, val: `width:auto;width:initial;` },
			{ class: `country-selector_selected .mm-out-of-stock-manager_country-selector_list_option img`, val: `width: 16px; min-width: 16px; margin-bottom: -1px` },
			{ class: 'country-selector_list_option span', val: `width: 75px;` },
			{ class: 'chevron', val: `margin-left: 8px; width: 0;height: 0;border-style: solid;border-width: 4px 3px 0 3px;border-color: #000 transparent transparent transparent;` },
			{ class: `country-selector:hover .mm-out-of-stock-manager_country-selector_list`, val: `display: block;` },
			{ class: 'action', val: `margin-top: 16px;` },
			{ class: 'action_submit', val: `width: 100%; display: block; color: #fff; border: 0; border-radius: 3px; cursor: pointer; text-transform: uppercase; letter-spacing: .5px; font-weight: 700; padding: 16px 8px; transition: background .2s ease, color .2s ease; ${this.css('color:', 'submit_color')} ${this.css('background:', 'submit_bg_color')} ${this.css('font-size:', 'submit_size', 'px')} ${this.css('padding:', 'submit_padding', 'px')}` },
			{ class: 'action_submit:hover', val: `${this.css('color:', 'submit_hover_color')} ${this.css('background:', 'submit_hover_bg_color')}` },
			{ class: 'invalid', val: `display: none; margin-top: 8px; ${this.css('color:', 'invalid_color')} ${this.css('font-size:', 'invalid_size', 'px')}` },
			{ class: 'success', val: `display: none; margin-top: 16px; text-align: center; ${this.css('padding:', 'submit_padding')} ${this.css('color:', 'success_color')} ${this.css('border: 1px solid', 'success_color')} ${this.css('font-size:', 'success_size', 'px')}` },
			{ class: 'error', val: `display: none; margin-top: 16px; text-align: center; ${this.css('padding:', 'submit_padding')} ${this.css('color:', 'invalid_color')} ${this.css('border: 1px solid', 'invalid_color')} ${this.css('font-size:', 'success_size', 'px')}` },
			{ class: `disabled .mm-out-of-stock-manager_field`, val: 'opacity: .3; pointer-events: none;'},
			{ class: `disabled .mm-out-of-stock-manager_action_submit`, val: 'opacity: .3; pointer-events: none;'},
			{ class: `loading .mm-out-of-stock-manager_action_submit`, val: `position: relative; ${this.css('color:', 'submit_bg_color')}` },
			{ class: `loading .mm-out-of-stock-manager_action_submit::after`, val: `content: ""; position: absolute; top: 50%; left: 50%; width: 20px; height: 20px; margin: -10px 0 0 -10px; border-radius: 50%; ${this.css('border: 5px solid', 'submit_color')} border-top-color: transparent; border-right-color: transparent; -webkit-animation:${this.handle}-rotate .6s linear infinite;animation:${this.handle}-rotate .6s linear infinite` },
			];
			var css = '';

			t.forEach(function(row) {
				css += '.' + (row.class ? this.handle + '_' + row.class : this.handle) + '{' + row.val + '}';
			}, this);

			css += '@-webkit-keyframes mm-out-of-stock-manager-rotate{from{-webkit-transform:rotate(0);transform:rotate(0)}to{-webkit-transform:rotate(359deg);transform:rotate(359deg)}}@keyframes mm-out-of-stock-manager-rotate{from{-webkit-transform:rotate(0);transform:rotate(0)}to{-webkit-transform:rotate(359deg);transform:rotate(359deg)}}';

			var e = document.createElement("style");
			document.getElementsByTagName("head")[0].appendChild(e);
			e.styleSheet ? e.styleSheet.cssText = css : e.appendChild(document.createTextNode(css));
		}

		dom() {
			return [
			{
				el: 'div', class: 'TOP', childs: [
				{ el: 'div', class: 'heading', val: this.config.labels.title },
				{ el: 'div', class: 'desc', val: this.config.labels.desc },
				{ el: 'div', class: 'field', id: 'email', childs: [
				{ el: 'div', class: 'label', childs: [
					{ el: 'span', class: 'label_check.checked' },
					{ el: 'text', val: this.config.labels.email }
				]},
				{ el: 'input', class: 'input', name: 'email' },
				{ el: 'div', class: 'invalid', val: this.config.labels.email_invalid }
				]},
				{ el: 'div', class: 'field', id: 'number', childs: [
				{ el: 'div', class: 'label', childs: [
					{ el: 'span', class: 'label_check' },
					{ el: 'text', val: this.config.labels.number }
				]},
				{ el: 'div', class: 'grid.hide-el', childs: [
				{ el: 'div', class: 'country-selector', childs: [
				{ el: 'div', class: 'country-selector_selected', childs: [
				{ el: 'div', class: 'country-selector_list_option', childs: [
				{ el: 'img', src: 'https://www.countryflags.io/us/flat/16.png' },
				{ el: 'span', val: '(+1)' },
				{ el: 'i', class: 'chevron' }
				]}
				]},
				{ el: 'div', class: 'country-selector_list'}
				]},
				{ el: 'input', class: 'input', name: 'phone' }
				]},
				{ el: 'div', class: 'invalid', val: this.config.labels.number_invalid }
				]},
				{ el: 'div', class: 'action', childs: [
				{ el: 'button', class: 'action_submit', val: this.config.labels.submit }
				]},
				{ el: 'div', class: 'success', val: this.config.labels.success },
				{ el: 'div', class: 'error', val: '' }
				]
			}
			];
		}

		generateDOM(dom) {
			var code = '';
			dom.forEach(function(node) {
				if (node.id) {
					if (!this.config[node.id + '_active']) {
						return;
					} else {
						if (node.id == 'number') {
							this.getCountries();
						}
					}
				}

				if (node.el == 'text') {

					code += node.val;

				} else {

					var element = document.createElement(node.el);

					if (node.class) {
						if (node.class == 'TOP') {
							element.classList.add(this.handle);
						} else {
							if (node.class == 'label_check.checked') {
								element.classList.add(this.handle + '_label_check', 'checked');
							} else if (node.class == 'grid.hide-el') {
								element.classList.add(this.handle + '_grid', 'hide-el');
							} else {
								element.classList.add(this.handle + '_' + node.class);
							}
						}
					}

					if (node.name) {
						element.name = node.name;
					}

					if (node.src) {
						element.src = node.src;
					}

					if (node.val) {
						element.innerHTML = node.val;
					}

					if (node.childs) {
						element.innerHTML = this.generateDOM(node.childs);
					}

					if (node.class == 'label_check.checked' || node.class == 'label_check') {

						if (node.class == 'label_check.checked' && this.config.number_active) {
							code += element.outerHTML;
						}
						if (node.class == 'label_check' && this.config.email_active) {
							code += element.outerHTML;
						}

					} else {
						code += element.outerHTML;
					}

				}
			}, this);

			return code;
		}

		getCountries() {
			var self = this;

			this.request('GET', 'https://restcountries.eu/rest/v2/all', function(err, data) {
				var json = JSON.parse(data);
				json.forEach(function(c) {
					self.countries.push({
						code: c.alpha2Code.toLowerCase(),
						call: c.callingCodes[0],
						name: c.nativeName
					});
				});
			});
		}

		generateCountriesDOM() {
			var html = '';

			this.countries.forEach(function(c) {
				var node = `
					<div class="mm-out-of-stock-manager_country-selector_list_option">
						<img src="https://www.countryflags.io/${c.code}/flat/32.png">
						<span>(+${c.call})</span>
						${c.name}
					</div>`;
				html += node.replace(/\r?\n|\r|\t/g, '');
			});

			return html;
		}

		injectCountries(el) {
			if (this.config['number_active']) {
				var countriesList = el.querySelector('.mm-out-of-stock-manager_country-selector_list');

				if (countriesList && countriesList.innerHTML == '') {
					countriesList.innerHTML = this.generateCountriesDOM();
				}
			}
		}

		maskingNumber(el) {
			if (this.config['number_active']) {
				var numberField = el.querySelector('[name="phone"]');

				if (numberField) {
					numberField.addEventListener('keyup', function(e) {
						var strippedValue = e.target.value.replace(/[^0-9]/g, "");
						var chars = strippedValue.split('');
						var count = 0, pattern = '***************';

						var val = '';
						for (var i=0; i<pattern.length; i++) {
							const c = pattern[i];
							if (chars[count]) {
								if (/\*/.test(c)) {
									val += chars[count];
									count++;
								} else {
									val += c;
								}
							}
						}

						e.target.value = val;

						if (numberField.createTextRange) {
							var range = numberField.createTextRange();
							range.move('character', val.length);
							range.select();
						} else if (numberField.selectionStart) {
							numberField.focus();
							numberField.setSelectionRange(val.length, val.length);
						}

					});
				}
			}
		}

		showDOM(instance, pre = null, equals = false) {
			if (!pre) {
				pre = document.querySelector('.mm-out-of-stock-manager_wrapper[data-instance-id="' + instance.id + '"]');
			}

			if (pre) {

				if (!equals) { 
					pre.innerHTML = this.form;
					pre.classList.remove('mm-out-of-stock-manager_disabled', 'mm-out-of-stock-manager_loading');
					this.injectCountries(pre);
					this.maskingNumber(pre);
				}
				pre.setAttribute('data-product-id', instance.productId);
				pre.setAttribute('data-variant-id', instance.variantId);
				pre.style.display = 'block';

			} else {

				var el = instance.form;

				if (el) {
					if (el.nextElementSibling && el.nextElementSibling.classList.contains('mm-out-of-stock-manager_wrapper')) {

						if (!equals) { 
							el.nextElementSibling.innerHTML = this.form;
							el.nextElementSibling.classList.remove('mm-out-of-stock-manager_disabled', 'mm-out-of-stock-manager_loading');
							this.injectCountries(el.nextElementSibling);
							this.maskingNumber(el.nextElementSibling);
						}
						el.nextElementSibling.setAttribute('data-product-id', instance.productId);
						el.nextElementSibling.setAttribute('data-variant-id', instance.variantId);
						el.nextElementSibling.style.display = 'block';

					} else {

						var wrapper = document.createElement('div');
						wrapper.classList.add('mm-out-of-stock-manager_wrapper');
						wrapper.setAttribute('data-instance-id', instance.id);
						wrapper.setAttribute('data-product-id', instance.productId);
						wrapper.setAttribute('data-variant-id', instance.variantId);
						wrapper.style.display = 'block';
						wrapper.innerHTML = this.form;
						this.injectCountries(wrapper);
						this.maskingNumber(wrapper);
						el.parentNode.insertBefore(wrapper, el.nextSibling);

					}
				}
			}
		}

		hideDOM(instanceId = null, pre = null) {
			if (!pre) {
				pre = document.querySelector('.mm-out-of-stock-manager_wrapper[data-instance-id="' + instanceId + '"]');
			}

			if (pre) {
				pre.style.display = 'none';
			}
		}

		watch() {
			var self = this;
			document.addEventListener('click', function(e) {
				setTimeout(function() {
					self.run();
				}, 25);
			})
		}

		watchUrl() {
			var self = this;

			history.pushState = ( f => function pushState(){
				var ret = f.apply(this, arguments);
				window.dispatchEvent(new Event('pushstate'));
				window.dispatchEvent(new Event('locationchange'));
				return ret;
			})(history.pushState);

			history.replaceState = ( f => function replaceState(){
				var ret = f.apply(this, arguments);
				window.dispatchEvent(new Event('replacestate'));
				window.dispatchEvent(new Event('locationchange'));
				return ret;
			})(history.replaceState);

			window.addEventListener('popstate',()=>{
				window.dispatchEvent(new Event('locationchange'));
			});

			window.addEventListener('locationchange', function(){
				var param = self.getParameterByName('variant');

				if (param && param != '') {
					if (self.instances[0].variants.indexOf(parseInt(param)) != -1) {
						var equals = self.instances[0].variantId == parseInt(param);
						self.instances[0].variantId = parseInt(param);
						self.showDOM(self.instances[0], null, equals);
					} else {
						self.hideDOM(0);
					}
				} else {
					self.hideDOM(0);
				}
			})
		}

		run() {
			var self = this;
			self.instances.forEach(function(instance, index) {
				if (!instance.select || !instance.select.value) {
					return;
				}

				if (instance.variants.indexOf(parseInt(instance.select.value)) != -1) {
					var equals = self.instances[index].variantId == parseInt(instance.select.value);
					self.instances[index].variantId = parseInt(instance.select.value);
					if (instance.form) {
						self.showDOM(instance, null, equals);
					}
				} else {
					self.hideDOM(instance.id);
				}
			});
		}

		trigger(action, param1 = null, param2 = null, param3 = null) {
			if (action == 'show') {
				var instance = {
					productId: param1,
					variantId: param2
				};

				this.showDOM(instance, param3);
			}

			if (action == 'hide') {
				this.hideDOM(null, param1);
			}
		}

		manageForm() {
			var self = this;

			document.addEventListener('click', function(e) {
				if (e.target.classList.contains('mm-out-of-stock-manager_action_submit')) {
					var wrapper = e.target.closest('.mm-out-of-stock-manager_wrapper'),
							emailField = wrapper.querySelector('[name="email"]'),
							numberField = wrapper.querySelector('[name="phone"]'),
							validEmail = false, valideNumber = false, data = {};

					if (emailField) {
						var emailCheck = emailField.previousElementSibling.querySelector('.mm-out-of-stock-manager_label_check');

						if (!emailCheck || emailCheck.classList.contains('checked')) {
							var re = /\S+@\S+\.\S+/;
							if (!re.test(emailField.value)) {
								emailField.nextElementSibling.style.display = 'block';
								return;
							} else {
								emailField.nextElementSibling.style.display = 'none';
								data.email = emailField.value;
							}
						}
					}

					if (numberField) {
						var numberCheck = numberField.parentElement.previousElementSibling.querySelector('.mm-out-of-stock-manager_label_check');

						if (!numberCheck || numberCheck.classList.contains('checked')) {
							var re = /^\d+$/;
							if (!re.test(numberField.value)) {
								numberField.closest('.mm-out-of-stock-manager_grid').nextElementSibling.style.display = 'block';
								return;
							} else {
								var prefix = wrapper.querySelector('.mm-out-of-stock-manager_country-selector_selected span');
								numberField.closest('.mm-out-of-stock-manager_grid').nextElementSibling.style.display = 'none';
								data.phone = prefix.innerText.replace('(', '').replace(')', '') + numberField.value;
							}
						}
					}

					data.product_id = wrapper.getAttribute('data-product-id');
					data.variant_id = wrapper.getAttribute('data-variant-id');
					data.tags = wrapper.getAttribute('data-tags') && wrapper.getAttribute('data-tags') != '' ? wrapper.getAttribute('data-tags') : '';
					data.action = 'add';

					if (data.product_id && data.variant_id) {
						if (data.email || data.phone) {
							var url = '//' + window.location.hostname + '/apps/mm-oosm?' + self.params(data);
							wrapper.classList.add('mm-out-of-stock-manager_loading', 'mm-out-of-stock-manager_disabled');
							wrapper.querySelector('.mm-out-of-stock-manager_error').style.display = '';
							self.request('POST', url, function(err, res) {
								var json = JSON.parse(res);

								if (json.error) {
									wrapper.classList.remove('mm-out-of-stock-manager_loading', 'mm-out-of-stock-manager_disabled');
									wrapper.querySelector('.mm-out-of-stock-manager_error').innerText = self.config.labels[json.id];
									wrapper.querySelector('.mm-out-of-stock-manager_error').setAttribute('data-error', json.id);
									wrapper.querySelector('.mm-out-of-stock-manager_error').style.display = 'block';
								} else {
									wrapper.classList.remove('mm-out-of-stock-manager_loading');
									wrapper.querySelector('.mm-out-of-stock-manager_error').style.display = '';
									wrapper.querySelector('.mm-out-of-stock-manager_success').style.display = 'block';

									var cookie = self.getCookie('mm-out-of-stock-manager_track');

									if (cookie) {
										var cookieData = JSON.parse(atob(cookie));
										if (cookieData[data.product_id]) {
											cookieData[data.product_id].push(data.variant_id);
										} else {
											cookieData[data.product_id] = [data.variant_id];
										}
									} else {
										var cookieData = {};
										cookieData[data.product_id] = [data.variant_id];
									}

									self.setCookie('mm-out-of-stock-manager_track', btoa(JSON.stringify(cookieData)), 30);
								}
							});
						}
					}
					
					return false;
				}

				if (e.target && (e.target.classList.contains('mm-out-of-stock-manager_country-selector_list_option') || e.target.closest('.mm-out-of-stock-manager_country-selector_list_option'))) {
					if (!e.target.closest('.mm-out-of-stock-manager_country-selector_selected')) {
						var parentOption = e.target.classList.contains('mm-out-of-stock-manager_country-selector_list_option') ? e.target : e.target.closest('.mm-out-of-stock-manager_country-selector_list_option'),
								parentSelected = parentOption.closest('.mm-out-of-stock-manager_country-selector'),
								image = parentOption.querySelector('img').getAttribute('src'),
								call = parentOption.querySelector('span').innerText;

						parentSelected.querySelector('img').setAttribute('src', image);
						parentSelected.querySelector('span').innerText = call;

					}
				}

				if (e.target && e.target.classList.contains('mm-out-of-stock-manager_label_check')) {
					var target = e.target,
							root = e.target.closest('.mm-out-of-stock-manager'),
							parentLabel = e.target.closest('.mm-out-of-stock-manager_label');

					if (target.classList.contains('checked')) {
						target.classList.remove('checked');
						parentLabel.nextElementSibling.classList.add('hide-el');

						var atLeastOneActive = false;
						root.querySelectorAll('.mm-out-of-stock-manager_label_check').forEach(function(label) {
							if (label.classList.contains('checked')) {
								atLeastOneActive = true;
							}
						});

						if (!atLeastOneActive) {
							root.querySelector('.mm-out-of-stock-manager_action').style.display = 'none';
						}
					} else {
						target.classList.add('checked');
						parentLabel.nextElementSibling.classList.remove('hide-el');
						root.querySelector('.mm-out-of-stock-manager_action').style.display = '';
					}
				}
			});
		}

		checkout() {
			var self = this, cookie = this.getCookie('mm-out-of-stock-manager_track');

			if (cookie) {
				var cookieData = JSON.parse(atob(cookie)),
						shopifyCheckout = window.Shopify.checkout;

				shopifyCheckout.line_items.forEach(function(item) {
					if (cookieData[item.product_id]) {
						var vidIndex = cookieData[item.product_id].indexOf(item.variant_id.toString());

						if (vidIndex != -1) {

							var trackData = {
								product_id: item.product_id,
								variant_id: item.variant_id,
								total: item.line_price,
								order_id: shopifyCheckout.order_id,
								created_at: shopifyCheckout.created_at,
								action: 'track'
							};

							var url = '//' + window.location.hostname + '/apps/mm-oosm?' + self.params(trackData);
							self.request('POST', url, function(err, res) {});

							cookieData[item.product_id].splice(vidIndex, 1);

							if (cookieData[item.product_id].length == 0) {
								delete cookieData[item.product_id];
							}

							if (Object.keys(cookieData).length == 0) {
								self.deleteCookie('mm-out-of-stock-manager_track');
							} else {
								self.setCookie('mm-out-of-stock-manager_track', btoa(JSON.stringify(cookieData)), 30);
							}
						}
					}
				})
			}
		}

		request(method, url, done) {
			var xhr = new XMLHttpRequest();
			xhr.open(method, url);
			xhr.onload = function () {
				done(null, xhr.response);
			};
			xhr.onerror = function () {
				done(xhr.response);
			};
			xhr.send();
		}

		ready(selector, fn) {
			var self = this;

			this.watcher.listeners.push({
				selector: selector,
				fn: fn
			});

			if (!this.watcher.observer) {
				this.watcher.observer = new this.watcher.MutationObserver(check);
				this.watcher.observer.observe(this.watcher.doc.documentElement, {
					childList: true,
					subtree: true
				});
			}

			check();

			function check() {
				for (var i = 0, len = self.watcher.listeners.length, listener, elements; i < len; i++) {
					listener = self.watcher.listeners[i];
					elements = self.watcher.doc.querySelectorAll(listener.selector);

					for (var j = 0, jLen = elements.length, element; j < jLen; j++) {
						element = elements[j];
						if (!element.ready) {
							element.ready = true;
							listener.fn.call(element, element);
						}
					}
				}
			}
		}

		getParameterByName(name, url) {
			if (!url) url = window.location.href;
			name = name.replace(/[\[\]]/g, '\\$&');
			var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
			results = regex.exec(url);
			if (!results) return null;
			if (!results[2]) return '';
			return decodeURIComponent(results[2].replace(/\+/g, ' '));
		}

		params(obj) {
			return Object.keys(obj).map(function(k) {
				return encodeURIComponent(k) + '=' + encodeURIComponent(obj[k]);
			}).join('&');
		}

		getCookie(name) {
			var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
			return v ? v[2] : null;
		}

		setCookie(name, value, days) {
			var d = new Date;
			d.setTime(d.getTime() + 24*60*60*1000*days);
			document.cookie = name + "=" + value + ";path=/;expires=" + d.toGMTString();
		}

		deleteCookie(name) { this.setCookie(name, '', -1); }
	}

	var MMOOSM_config = [[config]];

	window.MMOOSM = new MMOOSM(MMOOSM_config);
	window.MMOOSM.init();

})();