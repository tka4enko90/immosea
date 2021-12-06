<template>
    <StepWrap
            :title="title"
            :text="text"
            :buttonPrev="{
                ...buttonPrev
            }"
            :buttonNext="{
                ...buttonNext,
                click: handlerClick
            }"
            :showPrice="showPrice"
    >

        <div class="step__row">
            <div v-show="product_data.advertising_copy.price">
                <div class="form-checkbox form-checkbox--custom">
                    <input id="advertising_copy" type='checkbox' v-model="data.advertising_copy">
                    <label for="advertising_copy">
                        <span>{{ product_data.advertising_copy.price }} EUR</span>
                        <strong>{{product_data.advertising_copy.title ? product_data.advertising_copy.title : 'Werbetexte' }}</strong>
                        {{ product_data.advertising_copy.description ? product_data.advertising_copy.description : 'Angepasst auf die gängigen Immobilienportale' }}
                    </label>
                </div>
            </div>
            <div v-show="product_data.expose.price">
                <div class="form-checkbox form-checkbox--custom form-checkbox--green">
                    <input id="expose" type='checkbox' v-model="data.expose">
                    <label for="expose">
                        <span>{{ product_data.expose.price }} EUR</span>
                        <strong>{{product_data.expose.title ? product_data.expose.title : 'Exposé' }}</strong>
                        {{ product_data.expose.description ? product_data.expose.description : 'Professionelle Objekt broschüre im PDF Format' }}
                    </label>
                </div>
            </div>
            <div v-show="product_data.photography.price">
                <div class="form-checkbox form-checkbox--custom form-checkbox--yellow">
                    <input id="photography" type='checkbox' v-model="data.photography">
                    <label for="photography">
                        <span>{{ product_data.photography.price }} EUR</span>
                        <strong>{{product_data.photography.title ? product_data.photography.title : 'Fotografie' }}</strong>
                        {{ product_data.photography.description ? product_data.photography.description : 'Professionelles Fotoshooting für Ihr Objekt.' }}
                    </label>
                </div>
            </div>
            <div v-if="data.type !== 'property'">
                <div class="form-checkbox form-checkbox--custom form-checkbox--orange">
                    <input id="energy_certificate" type='checkbox' v-model="data.energy_certificate">
                    <label for="energy_certificate">
                        <span>{{ product_data.energy_certificate.price }} EUR</span>
                        <strong>{{product_data.energy_certificate.title ? product_data.energy_certificate.title : 'Energieausweis' }}</strong>
                        {{ product_data.energy_certificate.description ? product_data.energy_certificate.description : 'Erfüllen Sie die gesetzlichen Anforderungen an einen Energieausweis' }}
                    </label>
                </div>
            </div>
            <div v-show="product_data.floor_plan.price">
                <div class="form-checkbox form-checkbox--custom form-checkbox--gray">
                    <input id="floor_plan" type='checkbox' v-model="data.floor_plan">
                    <label for="floor_plan">
                        <span>{{ product_data.floor_plan.price }} EUR</span>
                        <strong>{{product_data.floor_plan.title ? product_data.floor_plan.title : 'Grundriss' }}</strong>
                        {{ product_data.floor_plan.description ?  product_data.floor_plan.description : 'Professionelle Grundriss-Coloration für Ihr Objekt' }}
                    </label>
                </div>
            </div>
            <div v-show="product_data.drone_footage.price">
                <div class="form-checkbox form-checkbox--custom form-checkbox--red">
                    <input id="drone_footage" type='checkbox' v-model="data.drone_footage">
                    <label for="drone_footage">
                        <span>{{ product_data.drone_footage.price }} EUR</span>
                        <strong>{{product_data.drone_footage.title ? product_data.drone_footage.title : 'Drohnenaufnahmen' }}</strong>
                        {{ product_data.floor_plan.description ? product_data.floor_plan.description : ' Das I-Tüpfelchen für Ihren Werbeauftritt' }}
                    </label>
                </div>
            </div>
            <div v-show="product_data.mailaddress.price">
                <div class="form-checkbox form-checkbox--custom form-checkbox--purple">
                    <input id="mailaddress" type='checkbox' v-model="data.mailaddress">
                    <label for="mailaddress">
                        <span>{{ product_data.mailaddress.price }} EUR</span>
                        <strong>{{ product_data.mailaddress.title ? product_data.mailaddress.title : 'Mailadresse' }}</strong>
                        {{ product_data.mailaddress.description ?  product_data.mailaddress.description : 'mit der Endung @beyourownmakler.com zur Anzeige im Online-Inserat, Mails werden an Ihre Mailadresse weitergeleitet' }}
                    </label>
                </div>
            </div>
            <div v-show="product_data.online_inserat.price">
                <div class="form-checkbox form-checkbox--custom form-checkbox--light-green">
                    <input id="online_inserat" type='checkbox' v-model="data.online_inserat">
                    <label for="online_inserat">
                        <span>{{ product_data.online_inserat.price }} EUR</span>
                        <strong>{{ product_data.online_inserat.title ? product_data.online_inserat.title : 'Online-Inserat' }}</strong>
                        {{ product_data.online_inserat.description ? product_data.online_inserat.description : '14 Tage bei Immoscout24 und Immowelt, nach 10 Tagen erhalt en Sie die Möglichkeit, um weitere 14 Tage zu verlängern' }}
                    </label>
                </div>
            </div>
        </div>
    </StepWrap>
</template>

<script>
  import StepWrap from '../Layout/StepWrap';
  import { getPriceByFieldName, getDescriptionByFieldName, getTitleByFieldName } from '../../utils';


  export default {
    name: 'app-step5',
    components: {
      StepWrap
    },
    props: ['title', 'text', 'buttonPrev', 'buttonNext', 'showPrice'],
    data() {return {}},
    computed: {
      data: {
        get() {
          return this.$store.state.cart
        },
        set(value) {
          this.$store.commit('SET_CART_OPTIONS', value)
        }
      },
      product_data() {
         return {
             advertising_copy: {
                 price: getPriceByFieldName(this.$store.state.products, 'advertising_copy'),
                 description: getDescriptionByFieldName(this.$store.state.products, 'advertising_copy'),
                 title: getTitleByFieldName(this.$store.state.products, 'advertising_copy'),
             },
             expose: {
                 price: getPriceByFieldName(this.$store.state.products, 'expose'),
                 description: getDescriptionByFieldName(this.$store.state.products, 'expose'),
                 title: getTitleByFieldName(this.$store.state.products, 'expose'),
             },
             floor_plan: {
                 price: getPriceByFieldName(this.$store.state.products, 'floor_plan'),
                 description: getDescriptionByFieldName(this.$store.state.products, 'floor_plan'),
                 title: getTitleByFieldName(this.$store.state.products, 'floor_plan'),
             },
             energy_certificate:{
                 price: this.$store.state.cart.type === 'house' && this.$store.state.cart.year < 1979
                     ? getPriceByFieldName(this.$store.state.products, 'energy_certificate_bg_house')
                     : getPriceByFieldName(this.$store.state.products, 'energy_certificate'),
                 description: this.$store.state.cart.type === 'house' && this.$store.state.cart.year < 1979
                     ? getDescriptionByFieldName(this.$store.state.products, 'energy_certificate_bg_house')
                     : getDescriptionByFieldName(this.$store.state.products, 'energy_certificate'),
                 title: this.$store.state.cart.type === 'house' && this.$store.state.cart.year < 1979
                     ? getTitleByFieldName(this.$store.state.products, 'energy_certificate_bg_house')
                     : getTitleByFieldName(this.$store.state.products, 'energy_certificate'),
             },
             photography:{
                 price: getPriceByFieldName(this.$store.state.products, 'photography'),
                 description: getDescriptionByFieldName(this.$store.state.products, 'photography'),
                 title: getTitleByFieldName(this.$store.state.products, 'photography'),
             },
             drone_footage:{
                 price: getPriceByFieldName(this.$store.state.products, 'drone_footage'),
                 description: getDescriptionByFieldName(this.$store.state.products, 'drone_footage'),
                 title: getTitleByFieldName(this.$store.state.products, 'drone_footage'),
             },
             mailaddress:{
                 price: getPriceByFieldName(this.$store.state.products, 'mailaddress'),
                 description: getDescriptionByFieldName(this.$store.state.products, 'mailaddress'),
                 title: getTitleByFieldName(this.$store.state.products, 'mailaddress'),
             },
             online_inserat:{
                 price: getPriceByFieldName(this.$store.state.products, 'online_inserat'),
                 description: getDescriptionByFieldName(this.$store.state.products, 'online_inserat'),
                 title: getTitleByFieldName(this.$store.state.products, 'online_inserat'),
             }
         }
      }
    },
    methods: {
      handlerClick() {
        if (!this.data.floor_plan) {
          this.$store.commit('SET_CART_OPTIONS', {image: null, uploads_images: [], surcharge_3d_floor: false})
        }

        this.buttonNext.click()
      },
    }
  }
</script>

