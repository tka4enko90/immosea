<template>

    <StepWrap
            :title="title"
            :buttonPrev="{
                ...buttonPrev
            }"
            :buttonNext="{
                ...buttonNext,
                disabled: !year
            }"
    >
        <div class="form-row">
            <InputText label="Baujahr"
                       v-model="year"
                       placeholder="Gebe hier das Baujahr der Immobilie an."
            />
        </div>

        <Checkbox label="Ich weiß das genaue Baujahr nicht" v-model="addOption" />
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

  export default {
    name: 'app-step3',
    components: {
      Radio, InputText, Checkbox,
      StepWrap
    },
    props: ['title', 'text', 'buttonPrev', 'buttonNext'],
    data() {
      return {
        // year: '',
        addOption: false,
        // yearCheck: ''
      }
    },
    computed: {
      year: {
        get() {
          return this.$store.state.year
        },
        set(value) {
          this.$store.commit('SET_YEAR', value)
        }
      }
    },
    methods: {}
  }
</script>

