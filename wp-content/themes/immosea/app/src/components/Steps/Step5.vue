<template>
    <div v-if="kindProperty">
        step5
    </div>
</template>

<script>
  import { kindProperty } from '../../Data/options'

  export default {
    name: 'app-step5',
    components: {
      // Radio
    },
    data() {
      return {
        kindProperty,
        radio: '',
      }
    },
    computed: {},
    methods: {}
  }
</script>

