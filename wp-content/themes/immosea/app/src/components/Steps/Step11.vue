<template>
    <StepWrap
            :title="title"
            :text="text"
            :buttonPrev="{...buttonPrev}"
            :buttonNext="{
                ...buttonNext,
                disabled: !collectData.street || !collectData.street_number || !collectData.post_code ||
                !collectData.town
            }"
            :showPrice="showPrice"
    >
        <div class="form__row form__row--flex">
            <div class="form__area">
                <InputText v-model="collectData.street" label="Straßenname" required />
            </div>
            <div class="form__area">
                <InputText v-model="collectData.street_number" label="Straßennummer" required />
            </div>
            <div class="form__area">
                <InputText v-model="collectData.post_code" label="Postleitzahl" required type="number" />
            </div>
            <div class="form__area">
                <InputText v-model="collectData.town" label="Ort" required />
            </div>
        </div>
        <div class="form__row" v-if="expose">
            <Checkbox v-model="collectData.postcode" label="Im Exposé bitte nur Postleitzahl und Ort angeben." />
        </div>
    </StepWrap>
</template>

<script>
  import StepWrap from '../Layout/StepWrap';
  import InputText from '../Form/InputText';
  import Checkbox from '../Form/Checkbox';


  export default {
    name: 'app-step11',
    components: { StepWrap, InputText, Checkbox },
    props: ['title', 'text', 'buttonPrev', 'buttonNext', 'showPrice'],
    data() {return {}},
    computed: {
      expose() { return this.$store.state.cart.expose },
      collectData: {
        get() {
          return this.$store.state.collectData
        },
        set(value) {
          this.$store.commit('SET_COLLECT_DATA', { value })
        }
      }
    },
    methods: {}
  }
</script>

