<template>
    <div class="form-input" :class="{
     'form-input--error': error,
     'form-input--inline': inline,
     'form-input--required': required,
     }">
        <label for="name" v-if="label">{{ label }}</label>
        <div>
            <input
                    :id="name"
                    :error="error"
                    :type="type"
                    :value="value"
                    :placeholder="placeholder"
                    :disabled="disabled"
                    @input="$emit('input', $event.target.value)"
                    @blur="$emit('blur')"
            >
            <div class="form__error" v-if="error && errorMessage">{{ errorMessage }}</div>
        </div>
    </div>
</template>
<script>

  export default {
    name: 'app-input',
    components: {},
    props: {
      disabled: Boolean,
      inline: Boolean,
      error: Boolean,
      errorMessage: String,
      required: Boolean,
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
      }
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
