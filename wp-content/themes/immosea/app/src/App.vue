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

Vue.use(Vuelidate)

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