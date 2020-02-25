<template>
  <div>
  	<div class="grid hspace-between-xs">

  		<div class="sidebar">

  			<component is="inc-sidebar" />

  		</div>

  		<div class="main">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a @click.prevent="$root.view = 'index'"><i class='bx bx-home-alt'></i></a></li>
          <li class="breadcrumb-item breadcrumb-item.active">Docs</li>
        </ul>

        <div class="grid m--3">
          <div class="col-24-xs">
            <div class="card m-3">
              <div class="card-header">
                <div class="grid vcenter-xs">
                  <div class="col-24-xs">
                    <h1 class="title">Documentation</h1>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <div class="faq">
                  <div class="row">
                    <strong class="q"><span class="badge badge-info">1</span> I want to display form in a different place. How to do it?</strong>
                    By default, Out of Stock Manager form is placed after the "add to cart" form. If you want it to display in different position of the website, use the code below: 
                    <pre class="my-2">&lt;div class="mm-out-of-stock-manager_wrapper" data-id="formId"&gt;&lt;/div&gt;</pre>
                    where
                    <pre class="mt-2 mb-4"><strong>formId</strong> - id of the add-to-cart form</pre>

                    If your theme uses the standard form for adding to cart, you can use this code: 
                    <pre class="my-2" v-pre>&lt;div class="mm-out-of-stock-manager_wrapper" data-id="product_form_{{ product.id }}"&gt;&lt;/div&gt;</pre>

                    One OOSM form can be attached to only one add-to-cart form
                  </div>

                  <div class="row">
                    <strong class="q"><span class="badge badge-info">2</span> I use Quick View in my theme. How can I connect it to the OOSM?</strong>
                    Quick Views are rendered dynamically with custom scripts, so it's not possible to detect where and when they show up. <br/>
                    However, if you use the codes above and place it in the Quick View template - app will detect the tag and Out of Stock Manager will work.
                  </div>

                  <div class="row">
                    <strong class="q"><span class="badge badge-info">3</span> I use custom theme and OOSM doesn't work</strong>
                    If your theme developer implemented different scripts to manage variant switcher, OOSM will not be able to detect when the unavailable variant was selected. <br/>
                    There are 2 solutions to this problem. First one is to watch the URL changes - use the code below if your theme updates URL parameter "variant" after changing product's variant.

                    <pre class="mt-2 mb-4" v-pre>&lt;div class="mm-out-of-stock-manager_wrapper" data-id="URL" data-first="{{ product.selected_or_first_available_variant.id }}"&gt;&lt;/div&gt;</pre>
                    Second option is to use triggers - check point 4 below.
                  </div>

                  <div class="row">
                    <strong class="q"><span class="badge badge-info">4</span> I want to manually manage appearance of the form. Are there any triggers?</strong>
                    Yes, you can use the codes to below to show / hide the Out of Stock Manager form.

                    <pre class="my-2" v-pre>MMOOSM.trigger('show', productId, variantId, domEl)</pre>
                    <pre class="my-2" v-pre>MMOOSM.trigger('hide', domEl)</pre>

                    where

                    <pre class="mt-2 mb-4" v-pre>
                      <strong>productId</strong> - id of the current product <br/>
                      <strong>variantId</strong> - id of the selected variant of the product <br/>
                      <strong>domEl</strong> - OOSM form dom element <br/>
                    </pre>
                    
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
module.exports = {}
</script>