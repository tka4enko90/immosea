<template>
    <div class="form-radio">
        <label :for="id">
            <input
                    :id="id"
                    type="radio"
                    :disabled="disabled"
                    :val="val"
                    :value="value"
                    :name="name"
                    @input="$emit('change', $event.target.value)"
            >
            <span v-if="label">{{ label }}</span>
        </label>
    </div>
</template>
<script>

  export default {
    name: 'app-radio',
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
      val: {}
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
