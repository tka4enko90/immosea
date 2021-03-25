<template>
    <div class="form-input" :class="{'form-input--error': error, 'form-input--inline': inline}">
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
        {{error}}

        <div class="form-error" v-if="errorMessage">{{ errorMessage }}</div>
    </div>
</template>
<script>

  export default {
    name: 'app-input',
    components: {},
    props: {
      disabled: Boolean,
      inline: Boolean,
      error: {},
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
      pattern: {}
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
