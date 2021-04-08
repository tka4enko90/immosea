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
            <div v-if="!floor_plan">
                <Uploader docs=true
                          title="BILD LADEN"
                          text="JPG, GIF, PNG, BMP je bis 50 VB nicht animert"
                          name="uploads_docs"
                          :loading="loading_uploads_docs"
                          @change="handleUploadDocs"
                />
            </div>
            <div v-if="!photography">
                <Uploader title="BILD LADEN"
                          text="JPG, GIF, PNG, BMP je bis 50 VB nicht animert"
                          name="uploads"
                          :loading="loading_uploads"
                          @change="handleUpload"
                />
            </div>
        </div>
        <div class="uploader__list" v-if="uploads_docs.length > 0">
            <div v-for="(file, key) in uploads_docs" :key="key">
                <UploaderPreview :file="file.attachment_url"
                                 :type="file.type"
                                 :name="file.name"
                                 @click="removeFile(key, uploads_docs, 'uploads_docs')" />
            </div>
        </div>
        <div class="uploader__list" v-if="uploads.length > 0">
            <div v-for="(file, key) in uploads" :key="key">
                <UploaderPreview :file="file.attachment_url"
                                 :type="file.type"
                                 :name="file.name"
                                 @click="removeFile(key, uploads, 'uploads')" />
            </div>
        </div>

    </StepWrap>
</template>

<script>
  import { Media } from '../../api';
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
      return {
        loading_uploads: false,
        loading_uploads_docs: false
      }
    },
    computed: {
      floor_plan() {
        return this.$store.state.cart.floor_plan
      },
      photography() {
        return this.$store.state.cart.photography
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
      handleUpload(file, name) {
        let array = this.$store.state.collectData.uploads
        this.loading_uploads = true

        Media.post(file)
          .then(res => {
            array.push(res.data)

            this.$store.commit('SET_COLLECT_DATA', {[`${name}`]: array})
            this.loading_uploads = false
          })
          .catch(err => {
            this.loading_uploads = false
            return new Error(err)
          })
      },
      handleUploadDocs(file, name) {
        let array = this.$store.state.collectData.uploads_docs
        this.loading_uploads_docs = true

        Media.post(file)
          .then(res => {
            array.push(res.data)

            this.$store.commit('SET_COLLECT_DATA', {[`${name}`]: array})
            this.loading_uploads_docs = false
          })
          .catch(err => {
            this.loading_uploads_docs = false
            return new Error(err)
          })
      },
      removeFile(key, array, name) {
        array.splice(key, 1);
        this.$store.commit('SET_COLLECT_DATA', { [`${name}`]: array })
      }
    }
  }
</script>

