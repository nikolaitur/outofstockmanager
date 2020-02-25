<template>
  <div>
  	<div class="grid hspace-between-xs">

  		<div class="sidebar">

  			<component is="inc-sidebar" />

  		</div>

  		<div class="main">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a @click.prevent="$root.view = 'index'"><i class='bx bx-home-alt'></i></a></li>
          <li class="breadcrumb-item breadcrumb-item.active">Installation</li>
        </ul>

        <div class="grid m--3">
          <div class="col-24-xs">
            <div class="card m-3">
              <div class="card-header">
                <div class="grid vcenter-xs">
                  <div class="col-24-xs">
                    <h1 class="title">FAQ</h1>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <div class="faq">
                  <div class="row">
                    <strong class="q"><span class="badge badge-info">1</span> I want to display form in different place. How to do it?</strong>
                    By default, Out of Stock Manager form is placed after the "add to cart" form. If you want it to display in different position of the website, use the code below: 
                    <pre class="mt-2">&lt;div class="mm-out-of-stock-manager_wrapper" data-id="&lt;&lt;formId&gt;&gt;">&lt;/div&gt;</pre>
                    <pre class="mt-2"><strong>&lt;&lt;formId&gt;&gt;</strong> - id of the add-to-cart form</pre>

                    If your theme uses the standard form for adding to cart, you can use this code: 
                    <pre class="mt-2" v-pre>&lt;div class="mm-out-of-stock-manager_wrapper" data-id="product_form_{{ product.id }}">&lt;/div&gt;</pre>
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