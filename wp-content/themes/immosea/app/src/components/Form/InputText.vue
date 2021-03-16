<template>
    <div class="form-input">
        <label for="name" v-if="label">{{ label }}</label>
        <input
                :id="name"
                :error="error"
                :type="type"
                :value="value"
                :placeholder="placeholder"
                :disabled="disabled"
                @input="$emit('input', $event.target.value)"
        >

        <div class="form-error" v-if="errorMessage">{{ errorMessage }}</div>
    </div>
</template>
<script>

  export default {
    name: 'app-input',
    components: {},
    props: {
      disabled: Boolean,
      error: Boolean,
      errorMessage: String,
      label: String,
      name: String,
      placeholder: String,
      type: {
        type: String,
        default: 'text'
      },
      value: {
        type: [String, Number],
        default: ''
      },
    },
    data: () => ({

    }),
    computed: {},
    watch: {},
    beforeCreate() {},
    created() {},
    beforeMount() {},
    mounted() {},
    beforeUpdate() {},
    updated() {},
    activated() {},
    deactivated() {},
    beforeDestroy() {},
    destroyed() {},
    methods: {

    }
  }
</script>

<style lang="scss" scoped></style>
