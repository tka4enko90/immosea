<template>
    <div class="form-checkbox" :class="{
        'form-checkbox--small': small,
        'form-checkbox--checked': checked
    }">
        <input
                type='checkbox'
                :disabled="disabled"
                :name='name'
                :id="id"
                :value="value"
                @input="$emit('change', $event.target.checked)"
        >
        <label v-if="label">{{ label }}</label>
    </div>
</template>
<script>

  export default {
    name: 'app-checkbox',
    components: {},
    model: {
      prop: 'checked',
      event: 'change'
    },
    props: {
      disabled: Boolean,
      error: Boolean,
      errorMessage: String,
      label: String,
      id: String,
      name: String,
      value: {},
      checked: Boolean,
      small: Boolean
    },
    data: () => ({}),
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
    methods: {}
  }
</script>

<style lang="scss" scoped></style>
