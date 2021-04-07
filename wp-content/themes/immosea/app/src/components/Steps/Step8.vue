<template>
    <StepWrap
            :title="title"
            :text="text"
            :buttonPrev="{...buttonPrev}"
            :buttonNext="{...buttonNext}"
            :showPrice="showPrice"
    >
        <ul class="form__list">
            <li>
                <Radio
                        label="Ja, ich möchte dennoch einen Energieausweis für meine Immobilie."
                        value=true id="yes" name="radio" v-model="energy_certificate"
                />
            </li>
            <li>
                <Radio
                        label="Nein, dann benötige ich keinen Energieausweis für meine Immobilie. "
                        value=false id="np" name="radio" v-model="energy_certificate"
                />
            </li>
        </ul>

    </StepWrap>
</template>

<script>
  import StepWrap from '../Layout/StepWrap';
  import Radio from '../Form/Radio';


  export default {
    name: 'app-step8',
    components: {
      StepWrap,
      Radio
    },
    props: ['title', 'text', 'buttonPrev', 'buttonNext', 'showPrice'],
    data() {return {}},
    computed: {
      energy_certificate: {
        get() {
          return this.$store.state.cart.energy_certificate
        },
        set(value) {
          this.$store.commit('SET_CERTIFICATE', value)
          this.$cookies.set('cart', this.$store.state.cart)
        }
      }
    },
    methods: {}
  }
</script>

