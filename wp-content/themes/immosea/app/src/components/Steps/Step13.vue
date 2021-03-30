<template>
    <StepWrap
            :title="title"
            :text="text"
            :buttonPrev="{...buttonPrev}"
            :buttonNext="{
                ...buttonNext,
            }"
            :showPrice="showPrice"
    >
        <div class="step__row">
            <div>
                <UploaderSingle :file="image"
                                title="Grundrisse laden"
                                text="JPG, GIF, PNG, BMP je bis 50 VB nicht animert"
                                @change="handleUpload"
                                @click="removeFile"
                />
            </div>
            <div>
                <Uploader multiple
                          title="Grundrisse laden"
                          text="Weitere Grundrisse für 25,- Euro pro Bild hinzufügen"
                          name="uploadsImages"
                          @change="handleFilesUpload"
                />
            </div>
        </div>
        <div class="uploader__list" v-if="uploadsImages.length > 0">
            <div v-for="(file, key) in uploadsImages" :key="key">
                <UploaderPreview :file="getImageUrl(file)"
                                 :type="file.type"
                                 :name="file.name"
                                 @click="removeFileFromArray(key, uploadsImages, 'uploadsImages')" />
            </div>
        </div>
        <div class="form__row">
            <div class="form__area form__area--expand">
                <Checkbox v-model="graphics3d"
                          label="Grundrisse lieber als 3D-Grafik erhalten? Pro Grundriss entsteht ein Aufpreis in Höhe von 30,- Euro"
                />
            </div>
        </div>
    </StepWrap>
</template>

<script>
  import StepWrap from '../Layout/StepWrap';
  import Uploader from '../Uploader/Uploader';
  import UploaderSingle from '../Uploader/UploaderSingle';
  import UploaderPreview from '../Uploader/UploaderPreview';
  import Checkbox from '../Form/Checkbox'


  export default {
    name: 'app-step13',
    components: {
      StepWrap, UploaderPreview,
      Uploader, UploaderSingle,
      Checkbox
    },
    props: ['title', 'text', 'buttonPrev', 'buttonNext', 'showPrice'],
    data() {return {}},
    computed: {
      image: {
        get() {
          return this.$store.state.cart.image
        }
      },
      uploadsImages: {
        get() {
          return this.$store.state.cart.uploadsImages
        }
      },
      graphics3d: {
        get() {
          return this.$store.state.cart.graphics3d
        },
        set(value) {
          this.$store.commit('SET_CART_OPTIONS', {graphics3d: value})
        }
      }
    },
    methods: {
      getImageUrl(file) {
        return URL.createObjectURL(file)
      },
      handleUpload(file) {
        console.log(file);
        this.$store.commit('SET_CART_OPTIONS', { image: file })
      },
      removeFile() {
        this.$store.commit('SET_CART_OPTIONS', { image: null })
      },
      handleFilesUpload(array, name) {
        this.$store.commit('SET_CART_OPTIONS', { [`${name}`]: array })
      },
      removeFileFromArray(key, array, name) {
        array.splice(key, 1);
        this.$store.commit('SET_CART_OPTIONS', { [`${name}`]: array })
      }
    }
  }
</script>

