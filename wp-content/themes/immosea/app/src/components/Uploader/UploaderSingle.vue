<template>
    <label class="uploader"
           :class="{
                'uploader--doc': docs,
                'uploader--no-empty': file,
                'uploader--loading': loading
            }"
    >
        <slot v-if="file">
            <img :src="file">
            <span class="uploader__preview-remove" @click="$emit('click')">x</span>
        </slot>
        <slot v-else>
            <span class="uploader__text">{{ text }}</span>
            <span class="uploader__title">{{ title }}</span>
            <span class="loader loader--small loader--position" v-if="loading"></span>

            <input type="file"
                   ref="file"
                   class="uploader__input"
                   accept="application/pdf, image/jpeg, image/png, image/gif, application/msword, image/bmp"
                   @change="handleUpload" />
        </slot>
    </label>
</template>
<script>

  export default {
    name: 'app-uploader-single',
    components: {},
    props: ['accept', 'title', 'text', 'docs', 'name', 'file', 'loading'],
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
    methods: {
      handleUpload() {
        const image    = this.$refs.file.files[0]
        const reader   = new FileReader()

        reader.onload = () => {
          this.$emit('change', reader.result, this.name)
        }

        reader.onerror = error => {
          console.error(error)
        }

        reader.readAsDataURL(image)
      },
    }
  }
</script>

<style lang="scss" scoped></style>
