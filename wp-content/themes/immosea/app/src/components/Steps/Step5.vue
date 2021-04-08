<template>
    <StepWrap
            :title="title"
            :text="text"
            :buttonPrev="{
                ...buttonPrev
            }"
            :buttonNext="{
                ...buttonNext,
            }"
            :showPrice="showPrice"
    >

        <div class="step__row">
            <div>
                <div class="form-checkbox form-checkbox--custom">
                    <input id="advertising_copy" type='checkbox' v-model="data.advertising_copy">
                    <label for="advertising_copy">
                        <span>{{ prices.advertising_copy }} EUR</span>
                        <strong>Werbetexte</strong>
                        Angepasst auf die gängigen Immobilien portale
                    </label>
                </div>
            </div>
            <div>
                <div class="form-checkbox form-checkbox--custom form-checkbox--green">
                    <input id="expose" type='checkbox' v-model="data.expose">
                    <label for="expose">
                        <span>{{ prices.expose }} EUR</span>
                        <strong>Exposé</strong>
                        Professionelle Objekt broschüre im PDF Format
                    </label>
                </div>
            </div>
            <div>
                <div class="form-checkbox form-checkbox--custom form-checkbox--yellow">
                    <input id="photography" type='checkbox' v-model="data.photography">
                    <label for="photography">
                        <span>{{ prices.photography }} EUR</span>
                        <strong>Fotografie</strong>
                        Professionelles Fotoshooting für dein Objekt.
                    </label>
                </div>
            </div>
            <div>
                <div class="form-checkbox form-checkbox--custom form-checkbox--orange">
                    <input id="energy_certificate" type='checkbox' v-model="data.energy_certificate">
                    <label for="energy_certificate">
                        <span>{{ prices.energy_certificate }} EUR</span>
                        <strong>Energieausweis</strong>
                        Erfülle die gesetzlichen Anforderungen an einen Energieausweis
                    </label>
                </div>
            </div>
            <div>
                <div class="form-checkbox form-checkbox--custom form-checkbox--gray">
                    <input id="floor_plan" type='checkbox' v-model="data.floor_plan">
                    <label for="floor_plan">
                        <span>{{ prices.floor_plan }} EUR</span>
                        <strong>Grundriss</strong>
                        Professionelle Grundriss-Coloration für dein Objekt
                    </label>
                </div>
            </div>
            <div>
                <div class="form-checkbox form-checkbox--custom form-checkbox--red">
                    <input id="drone_footage" type='checkbox' v-model="data.drone_footage">
                    <label for="drone_footage">
                        <span></span>
                        <strong>Drohnenaufnahmen</strong>
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
          console.log(value);
          this.$store.commit('SET_CART_OPTIONS', value)
          this.$cookies.set('cart', this.$store.state.cart)
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
          photography: getPriceByFieldName(this.$store.state.products, `photography_${this.$store.state.cart.type}`)
        }
      }
    },
    methods: {

    }
  }
</script>

