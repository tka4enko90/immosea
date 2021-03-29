<template>

    <StepWrap
            :title="title"
            :text="text"
            :buttonPrev="{
                ...buttonPrev
            }"
            :buttonNext="{
                ...buttonNext,
                disabled: !year
            }"
            :showPrice="showPrice"
    >
        <div class="form__row">
            <InputText label="Baujahr"
                       v-model="year"
                       type="number"
                       placeholder="Gebe hier das Baujahr der Immobilie an."
                       @blur="$v.year.$touch()"
                       :error="!$v.year.between"
                       errorMessage="Please enter valid year"
            />
        </div>

        <div class="form__row">
            <Checkbox label="Ich weiß das genaue Baujahr nicht" v-model="addOption" />
        </div>
        <div v-show="addOption">
            <div class="form__row">
                <Radio
                        label="bis 1978"
                        value="1978"
                        name="radio"
                        v-model="year"
                />
            </div>
            <div class="form__row">
                <Radio
                        label="ab 1979"
                        value="1979"
                        name="radio"
                        v-model="year"
                />
            </div>

        </div>
    </StepWrap>
</template>

<script>
  import Radio from '../Form/Radio.vue';
  import Checkbox from '../Form/Checkbox.vue';
  import InputText from '../Form/InputText.vue';
  import StepWrap from '../Layout/StepWrap';
  import { required, between } from 'vuelidate/lib/validators';


  export default {
    name: 'app-step3',
    components: {
      Radio,
      InputText,
      Checkbox,
      StepWrap
    },
    props: ['title', 'text', 'buttonPrev', 'buttonNext', 'showPrice'],
    data() {
      return {
        addOption: false,
        errors: []
      }
    },
    computed: {
      year: {
        get() {
          return this.$store.state.cart.year
        },
        set(value) {
          this.$store.commit('SET_CART_OPTIONS', { year: value })
        }
      }
    },
    validations: {
      year: {
        required,
        between: between(1500, new Date().getFullYear()),
      }
    },
    methods: {}
  }
</script>

