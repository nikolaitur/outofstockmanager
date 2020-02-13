<template>
  <div>
    <input class="mt-2 w-100 jscolor" v-model="model" :placeholder="placeholder" @change="update">
  </div>
</template>

<script>
module.exports = {
  props: ['model', 'index', 'placeholder'],
  methods: {
  	update: function(e) {
  		this.$parent.item.style[this.index] = e.target.value
  	}
  },
  mounted: function() {
    new jscolor(this.$el.firstChild, {
    	hash:true
    })
  }
}
</script>