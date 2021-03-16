<template>
    <div class="form-checkbox">
        <label>
            <input
                    type='checkbox'
                    :name='name'
                    :disabled="disabled"
                    :id="id"
                    :value="value"
                    @input="$emit('change', $event.target.checked)"
            >
            <span v-if="label">{{ label }}</span>
        </label>
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
      checked: Boolean
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
