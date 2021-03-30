<template>
    <label class="uploader"
           :class="{
                'uploader--doc': docs,
                'uploader--no-empty': file
            }"
    >
        <slot v-if="file">
            <img :src="getImageUrl(file)">
            <span class="uploader__preview-remove" @click="$emit('click')">x</span>
        </slot>
        <slot v-else>
            <span class="uploader__text">{{ text }}</span>
            <span class="uploader__title">{{ title }}</span>

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
    props: ['accept', 'title', 'text', 'docs', 'name', 'file'],
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
      getImageUrl(file) {
        return URL.createObjectURL(file)
      },
      handleUpload() {
        let image = this.$refs.file.files[0];
        this.$emit('change', image)
      },
    }
  }
</script>

<style lang="scss" scoped></style>
