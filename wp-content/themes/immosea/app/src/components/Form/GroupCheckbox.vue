<template>
    <div class="form-group">
        <div v-for="{ label, value, id } in options" :key="id">
            <div class="form-checkbox">
                <input
                        type="checkbox"
                        :value="value"
                        v-model="model"
                >
                <label v-if="label">{{ label }}</label>
            </div>
        </div>
    </div>
</template>
<script>
  export default {
    name: 'app-group-checkbox',
    model: {
      prop: 'checked',
      event: 'change',
    },
    props: ['label', 'value', 'checked', 'options'],
    computed: {
      model: {
        get() {
          return this.checked;
        },
        set(val) {
          this.$emit('change', val);
        },
      },
    },
  }
</script>

<style lang="scss" scoped>
    .form-group {
        > div {
            margin-bottom: 16px;
        }
    }
</style>
