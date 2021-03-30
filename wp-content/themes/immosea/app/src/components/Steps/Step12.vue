<template>
    <StepWrap
            :title="title"
            :text="text"
            :buttonPrev="{...buttonPrev}"
            :buttonNext="{
                ...buttonNext,
                disabled: uploadsDocs.length < 1
            }"
            :showPrice="showPrice"
    >
        <div class="step__row">
            <div>
                <Uploader docs=true
                          title="BILD LADEN"
                          text="JPG, GIF, PNG, BMP je bis 50 VB nicht animert"
                          name="uploadsDocs"
                          @change="handleFilesUpload"
                />
            </div>
            <div v-if="!photography">
                <Uploader title="BILD LADEN"
                          text="JPG, GIF, PNG, BMP je bis 50 VB nicht animert"
                          name="uploads"
                          @change="handleFilesUpload"
                />
            </div>
        </div>
        <div class="uploader__list" v-if="uploadsDocs.length > 0">
            <div v-for="(file, key) in uploadsDocs" :key="key">
                <UploaderPreview :file="getImageUrl(file)"
                                 :type="file.type"
                                 :name="file.name"
                                 @click="removeFile(key, uploadsDocs, 'uploadsDocs')" />
            </div>
        </div>
        <div class="uploader__list" v-if="uploads.length > 0">
            <div v-for="(file, key) in uploads" :key="key">
                <UploaderPreview :file="getImageUrl(file)"
                                 :type="file.type"
                                 :name="file.name"
                                 @click="removeFile(key, uploads, 'uploads')" />
            </div>
        </div>

    </StepWrap>
</template>

<script>
  import StepWrap from '../Layout/StepWrap';
  import Uploader from '../Uploader/Uploader';
  import UploaderPreview from '../Uploader/UploaderPreview';

  export default {
    name: 'app-step12',
    components: {
      StepWrap, UploaderPreview, Uploader
    },
    props: ['title', 'text', 'buttonPrev', 'buttonNext', 'showPrice'],
    data() {
      return {}
    },
    computed: {
      photography() {
        return this.$store.state.cart.photography
      },
      uploadsDocs: {
        get() {
          return this.$store.state.collectData.uploadsDocs
        }
      },
      uploads: {
        get() {
          return this.$store.state.collectData.uploads
        }
      }
    },
    methods: {
      getImageUrl(file) {
        return URL.createObjectURL(file)
      },
      handleFilesUpload(array, name) {
        this.$store.commit('SET_COLLECT_DATA', { [`${name}`]: array })
      },
      removeFile(key, array, name) {
        array.splice(key, 1);
        this.$store.commit('SET_COLLECT_DATA', { [`${name}`]: array })
      }
    }
  }
</script>

