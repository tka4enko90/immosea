<template>
    <StepWrap
            :title="title"
            :text="text"
            :buttonPrev="{...buttonPrev}"
            :buttonNext="{
                ...buttonNext,
                disabled: !collectData.address
            }"
            :showPrice="showPrice"
    >
        <div class="form__row">
            <InputText v-model="collectData.address" label="Adresse" placeholder="Adresse eingeben" required />
        </div>
        <div class="form__row">
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

