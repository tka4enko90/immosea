<template>
    <StepWrap
            :title="title"
            :text="text"
            :buttonPrev="{...buttonPrev}"
            :buttonNext="{
                ...buttonNext,
                disabled: uploads_docs.length < 1
            }"
            :showPrice="showPrice"
    >
        <div class="step__row">
            <div>
                <Uploader docs=true
                          title="BILD LADEN"
                          text="JPG, GIF, PNG, BMP je bis 50 VB nicht animert"
                          name="uploads_docs"
                          @change="handleFilesUpload"
                />
            </div>
            <div v-if="!floor_plan">
                <Uploader title="BILD LADEN"
                          text="JPG, GIF, PNG, BMP je bis 50 VB nicht animert"
                          name="uploads"
                          @change="handleFilesUpload"
                />
            </div>
        </div>
        <div class="uploader__list" v-if="uploads_docs.length > 0">
            <div v-for="(file, key) in uploads_docs" :key="key">
                <UploaderPreview :file="getImageUrl(file)"
                                 :type="file.type"
                                 :name="file.name"
                                 @click="removeFile(key, uploads_docs, 'uploads_docs')" />
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
      floor_plan() {
        return this.$store.state.cart.floor_plan
      },
      uploads_docs: {
        get() {
          return this.$store.state.collectData.uploads_docs
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

