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
                    <pre class="my-2">&lt;div class="mm-out-of-stock-manager_wrapper" data-id="&lt;&lt;formId&gt;&gt;">&lt;/div&gt;</pre>
                    where
                    <pre class="mt-2 mb-4"><strong>&lt;&lt;formId&gt;&gt;</strong> - id of the add-to-cart form</pre>

                    If your theme uses the standard form for adding to cart, you can use this code: 
                    <pre class="my-2" v-pre>&lt;div class="mm-out-of-stock-manager_wrapper" data-id="product_form_{{ product.id }}">&lt;/div&gt;</pre>

                    One OOSM form can be attached to only one add-to-cart form
                  </div>

                  <div class="row">
                    <strong class="q"><span class="badge badge-info">2</span> I use Quick View in my theme. How can I connect it to the OOSM?</strong>
                    Quick Views are rendered dynamically with custom scripts, so it's not possible to detect where and when they show up. <br/>
                    However, if you use the codes above and place it in the Quick View template - app will detect the tag and Out of Stock Manager will work.
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