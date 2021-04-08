<template>
    <div class="form-radio" :class="{
        'form-radio--small': small,
        'form-radio--checked': value === checked
    }">
        <input
                type="radio"
                :disabled="disabled"
                :val="val"
                :value="value"
                :name="name"
                @input="$emit('change', $event.target.value)"
        >
        <label :for="value">{{ label }}</label>
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
      small: Boolean,
      errorMessage: String,
      label: String,
      id: String,
      name: String,
      value: {},
      val: {},
      checked: {}
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

<style lang="scss"></style>
