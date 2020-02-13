<template>
  <div>
  	<div class="grid hspace-between-xs">

  		<div class="sidebar">

  			<component is="inc-sidebar" />

  		</div>

  		<div class="main">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a @click.prevent="$root.view = 'index'"><i class='bx bx-home-alt'></i></a></li>
          <li class="breadcrumb-item breadcrumb-item.active">Help</li>
        </ul>

        <div class="grid m--3">
          <div class="col-14-m">
            <div class="card m-3">
              <div class="card-header">
                <div class="grid vcenter-xs">
                  <div class="col-24-xs">
                    <h1 class="title">Contact us</h1>
                  </div>
                </div>
              </div>
              <div class="card-body">
                If you have any problems with our Out of Stock Manager app, don't hesitate and contact us at:

                <div class="alert alert-warning d-inline-block mt-4">
                  <div class="grid vcenter-xs flex-nowrap">
                    <i class='bx bx-mail-send icon'></i>
                    <div class="message">
                      <a href="mailto:apps@minionmade.com">apps@minionmade.com</a>
                    </div>
                  </div>
                </div>

                <br/><br/>Do you have ideas how can we improve our app? Drop us a message too!

              </div>
            </div>
          </div>
          <div class="col-10-m">
            <div class="card m-3">
              <div class="card-header">
                <div class="grid vcenter-xs">
                  <div class="col-24-xs">
                    <h1 class="title">Who we are?</h1>
                  </div>
                </div>
              </div>
              <div class="card-body mm-about">
                <strong>Minion Made</strong> is an award winning website development agency in New York. We are Shopify Experts & Shopify Plus Experts focused on building dynamic Shopify custom designs for our clients all over the world. <br/><br/>
                Read more on our <a href="https://minionmade.com/" target="_blank">website</a>.

                <ul class="social">
                  <li><a href="http://facebook.com/minionmade" target="_blank"><i class='bx bxl-facebook-square' ></i></a></li>
                  <li><a href="http://twitter.com/minionmade" target="_blank"><i class='bx bxl-twitter' ></i></a></li>
                  <li><a href="http://instagram.com/minionmade" target="_blank"><i class='bx bxl-instagram' ></i></a></li>
                  <li><a href="http://github.com/minionmade" target="_blank"><i class='bx bxl-github' ></i></a></li>
                  <li><a href="http://linkedin.com/company/minion-made" target="_blank"><i class='bx bxl-linkedin' ></i></a></li>
                </ul>
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

<style>
  .mm-about { line-height: 1.5 }
  .mm-about .social { margin-bottom: -8px }
  .mm-about .social li { display: inline-block; font-size: 20px; }
</style>