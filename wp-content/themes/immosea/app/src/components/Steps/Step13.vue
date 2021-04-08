<template>
    <StepWrap
            :title="title"
            :text="text"
            :buttonPrev="{...buttonPrev}"
            :buttonNext="{
                ...buttonNext,
                disabled: !image,
            }"
            :showPrice="showPrice"
    >
        <!--<div>-->
            <!--<label class="uploader"-->
            <!--&gt;-->


                    <!--<span class="uploader__text">test</span>-->
                    <!--<span class="uploader__title">title</span>-->

                    <!--<input type="file"-->
                           <!--ref="file"-->
                           <!--class="uploader__input"-->
                           <!--accept="application/pdf, image/jpeg, image/png, image/gif, application/msword, image/bmp"-->
                           <!--@change="dd" />-->
            <!--</label>-->
        <!--</div>-->
        <!--<img :src="src" alt="">-->
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
                          :text="labels.further_floor_plan"
                          name="uploads_images"
                          @change="handleFilesUpload"
                />
            </div>
        </div>
        <div class="uploader__list" v-if="uploads_images.length > 0">
            <div v-for="(file, key) in uploads_images" :key="key">
                <UploaderPreview :file="getImageUrl(file)"
                                 :type="file.type"
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
    data() {return {src:''}},
    computed: {
      image: {
        get() {
          return this.$store.state.cart.image
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
      getImageUrl(file) {
        return URL.createObjectURL(file)
      },
      handleUpload(file) {
        // this.$store.dispatch('postImage', file)
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
      },
      dd() {
        let image = this.$refs.file.files[0];

        const reader = new FileReader()
        console.log(reader);

        reader.onload = (e) => {
          // console.log(e.target.result);
          this.src = e.target.result;
          this.$store.dispatch('postImage', e.target.result)
        }

        reader.onerror = function(error) {
          alert(error);
        };
        reader.readAsDataURL(image);
      }
    }
  }
</script>

