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
            <div v-show="prices.advertising_copy">
                <div class="form-checkbox form-checkbox--custom">
                    <input id="advertising_copy" type='checkbox' v-model="data.advertising_copy">
                    <label for="advertising_copy">
                        <span>{{ prices.advertising_copy }} EUR</span>
                        <strong>Werbetexte</strong>
                        Angepasst auf die gängigen Immobilienportale
                    </label>
                </div>
            </div>
            <div v-show="prices.expose">
                <div class="form-checkbox form-checkbox--custom form-checkbox--green">
                    <input id="expose" type='checkbox' v-model="data.expose">
                    <label for="expose">
                        <span>{{ prices.expose }} EUR</span>
                        <strong>Exposé</strong>
                        Professionelle Objekt broschüre im PDF Format
                    </label>
                </div>
            </div>
            <div v-show="prices.photography">
                <div class="form-checkbox form-checkbox--custom form-checkbox--yellow">
                    <input id="photography" type='checkbox' v-model="data.photography">
                    <label for="photography">
                        <span>{{ prices.photography }} EUR</span>
                        <strong>Fotografie</strong>
                        Professionelles Fotoshooting für Ihr Objekt.
                    </label>
                </div>
            </div>
            <div v-if="data.type !== 'property'">
                <div class="form-checkbox form-checkbox--custom form-checkbox--orange">
                    <input id="energy_certificate" type='checkbox' v-model="data.energy_certificate">
                    <label for="energy_certificate">
                        <span>{{ prices.energy_certificate }} EUR</span>
                        <strong>Energieausweis</strong>
                        Erfüllen Sie die gesetzlichen Anforderungen an einen Energieausweis
                    </label>
                </div>
            </div>
            <div v-show="prices.floor_plan">
                <div class="form-checkbox form-checkbox--custom form-checkbox--gray">
                    <input id="floor_plan" type='checkbox' v-model="data.floor_plan">
                    <label for="floor_plan">
                        <span>{{ prices.floor_plan }} EUR</span>
                        <strong>Grundriss</strong>
                        Professionelle Grundriss-Coloration für Ihr Objekt
                    </label>
                </div>
            </div>
            <div v-show="prices.drone_footage">
                <div class="form-checkbox form-checkbox--custom form-checkbox--red">
                    <input id="drone_footage" type='checkbox' v-model="data.drone_footage">
                    <label for="drone_footage">
                        <span>{{ prices.drone_footage }} EUR</span>
                        <strong>Drohnenaufnahmen</strong>
                        Das I-Tüpfelchen für Ihren Werbeauftritt
                    </label>
                </div>
            </div>
            <div v-show="prices.mailaddress">
                <div class="form-checkbox form-checkbox--custom form-checkbox--purple">
                    <input id="mailaddress" type='checkbox' v-model="data.mailaddress">
                    <label for="mailaddress">
                        <span>{{ prices.mailaddress }} EUR</span>
                        <strong>Mailadresse</strong>
                        mit der Endung @beyourownmakler.com zur Anzeige im Online-Inserat, Mails werden an Ihre Mailadresse weitergeleitet
                    </label>
                </div>
            </div>
            <div v-show="prices.online_inserat">
                <div class="form-checkbox form-checkbox--custom form-checkbox--light-green">
                    <input id="online_inserat" type='checkbox' v-model="data.online_inserat">
                    <label for="online_inserat">
                        <span>{{ prices.online_inserat }} EUR</span>
                        <strong>Online-Inserat</strong>
                        14 Tage bei Immoscout24 und Immowelt, nach 10 Tagen erhalt en Sie die Möglichkeit, um weitere 14 Tage zu verlängern
                    </label>
                </div>
            </div>
        </div>
    </StepWrap>
</template>

<script>
  import StepWrap from '../Layout/StepWrap';
  import { getPriceByFieldName } from '../../utils';


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
      prices() {
        return {
          advertising_copy: getPriceByFieldName(this.$store.state.products, 'advertising_copy'),
          expose: getPriceByFieldName(this.$store.state.products, 'expose'),
          floor_plan: getPriceByFieldName(this.$store.state.products, 'floor_plan'),
          energy_certificate: this.$store.state.cart.type === 'house' && this.$store.state.cart.year < 1979
                                ? getPriceByFieldName(this.$store.state.products, 'energy_certificate_bg_house')
                                : getPriceByFieldName(this.$store.state.products, 'energy_certificate'),
          photography: getPriceByFieldName(this.$store.state.products, `photography_${this.$store.state.cart.type}`),
          drone_footage: getPriceByFieldName(this.$store.state.products, 'drone_footage'),
          mailaddress: getPriceByFieldName(this.$store.state.products, 'mailaddress'),
          online_inserat: getPriceByFieldName(this.$store.state.products, 'online_inserat')
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

