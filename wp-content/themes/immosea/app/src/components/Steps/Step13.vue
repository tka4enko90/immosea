<template>
    <StepWrap
            :title="title"
            :text="text"
            :buttonPrev="{...buttonPrev}"
            :buttonNext="{
                ...buttonNext,
                disabled: !image,
                click: handlerClick
            }"
            :showPrice="showPrice"
    >
        <div class="step__row">
            <div>
                <Uploader title="Grundrisse laden"
                          :loading="loading"
                          text="JPG, PNG je bis 50 VB nicht animert"
                          @change="handleUpload"
                          @click="removeFile"
                />
            </div>
            <div>
                <Uploader title="Grundrisse laden"
                          :text="labels.further_floor_plan"
                          :loading="loading2"
                          :disabled="!image"
                          name="uploads_images"
                          @change="handleFilesUpload"
                />
            </div>
        </div>
        <div class="uploader__list" v-if="uploads_images.length > 0 || image">
            <div v-if="image">
                <UploaderPreview :file="image.attachment_url"
                                 :type="image.attachment_mine_type"
                                 :name="image.name"
                                 @click="removeFile" />
            </div>
            <div v-for="(file, key) in uploads_images" :key="key">
                <UploaderPreview :file="file.attachment_url"
                                 :type="file.attachment_mine_type"
                                 :name="file.name"
                                 @click="removeFileFromArray(key, uploads_images, 'uploads_images')" />
            </div>
        </div>
        <div class="form__row">
            <div class="form__area form__area--expand">
                <Checkbox v-model="surcharge_3d_floor"
                          :label="labels.surcharge_3d_floor"
                />
            </div>
        </div>
    </StepWrap>
</template>

<script>
  import { getPriceByFieldName } from '../../utils';
  import { Media } from '../../api';
  import StepWrap from '../Layout/StepWrap';
  import Uploader from '../Uploader/Uploader';
  import UploaderPreview from '../Uploader/UploaderPreview';
  import Checkbox from '../Form/Checkbox'


  export default {
    name: 'app-step13',
    components: {
      StepWrap, UploaderPreview,
      Uploader,
      Checkbox
    },
    props: ['title', 'text', 'buttonPrev', 'buttonNext', 'showPrice'],
    data() {
      return {
        loading: false,
        loading2: false,
      }
    },
    computed: {
      image: {
        get() {
          return this.$store.state.cart.image
        },
        set(value) {
          this.$store.commit('SET_CART_OPTIONS', { value })
        }
      },
      uploads_images: {
        get() {
          return this.$store.state.cart.uploads_images
        }
      },
      surcharge_3d_floor: {
        get() {
          return this.$store.state.cart.surcharge_3d_floor
        },
        set(value) {
          this.$store.commit('SET_CART_OPTIONS', {surcharge_3d_floor: value})
        }
      },
      labels() {
        return {
          surcharge_3d_floor:
            `Grundrisse lieber als 3D-Grafik erhalten? Pro Grundriss entsteht ein Aufpreis in Höhe von ${getPriceByFieldName(this.$store.state.products, 'surcharge_3d_floor')},- Euro`,
          further_floor_plan: `Weitere Grundrisse für ${getPriceByFieldName(this.$store.state.products, 'further_floor_plan')},- Euro pro Bild hinzufügen`
        }
      }
    },
    methods: {
      handlerClick() {
        this.$store.commit('SET_COLLECT_DATA', {uploads_docs: [], uploads: []})
        this.buttonNext.click()
      },
      handleUpload(file) {
        this.loading = true

        Media.post(file)
          .then(res => {
            this.$store.commit('SET_CART_OPTIONS', {image: res.data})
            this.loading = false
          })
          .catch(err => {
            this.loading = false
            return new Error(err)
          })
      },
      handleFilesUpload(file, name) {
        let array     = this.$store.state.cart.uploads_images
        this.loading2 = true

        Media.post(file)
          .then(res => {
            array.push(res.data)

            this.$store.commit('SET_CART_OPTIONS', {[`${name}`]: array, further_floor_plan: array.length > 0})
            this.loading2 = false
          })
          .catch(err => {
            this.loading2 = false
            return new Error(err)
          })
      },
      removeFile() {
        this.$store.commit('SET_CART_OPTIONS', {image: null})
      },
      removeFileFromArray(key, array, name) {
        array.splice(key, 1);
        this.$store.commit('SET_CART_OPTIONS', {[`${name}`]: array, further_floor_plan: array.length > 0})
      },
    }
  }
</script>

