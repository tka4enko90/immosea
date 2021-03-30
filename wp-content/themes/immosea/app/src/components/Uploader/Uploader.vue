<template>
    <label class="uploader" :class="{'uploader--doc': docs}">
        <span class="uploader__text">{{ text }}</span>
        <span class="uploader__title">{{ title }}</span>

        <input type="file"
               multiple
               ref="files"
               class="uploader__input"
               accept="application/pdf, image/jpeg, image/png, image/gif, application/msword, image/bmp"
               @change="handleFilesUpload" />
    </label>
</template>
<script>

  export default {
    name: 'app-uploader',
    components: {},
    props: ['accept', 'title', 'text', 'docs', 'name'],
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
      handleFilesUpload() {
        let array = []
        let uploadedFiles = this.$refs.files.files;
        for( var i = 0; i < uploadedFiles.length; i++ ){
          array.push(uploadedFiles[i]);
        }
        this.$emit('change', array, this.name)
      },
    }
  }
</script>

<style lang="scss" scoped></style>
