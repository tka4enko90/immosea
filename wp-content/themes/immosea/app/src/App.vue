<template>
  <div id="app" class="app">
    <Form/>
  </div>
</template>

<script>

import Vue from 'vue'
import Form from './components/Form'
import store from './store'
import Vuelidate from 'vuelidate'
import VueCookies from 'vue-cookies'


Vue.use(Vuelidate)
Vue.use(VueCookies)


export default {
  name: 'App',
  store,
  components: {
    Form
  },
}
</script>
<style lang="scss">
  @import "assets/styles/main";
</style>