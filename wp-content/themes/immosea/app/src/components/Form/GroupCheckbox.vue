<template>
    <div class="form-group">
        <div class="form-checkbox" v-for="{ label, value, id } in options" :key="id">
            <label>
                <input
                    type="checkbox"
                    :value="value"
                    v-model="model"
                >
                    <span v-if="label">{{ label }}</span>
                </label>
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
<style scoped></style>