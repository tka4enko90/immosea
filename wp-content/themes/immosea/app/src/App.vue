<template>
  <div id="app" class="app">
    <Form/>
  </div>
</template>

<script>
import Form from './components/Form'
import store from './store'

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