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
                        value=true id="yes" name="radio" v-model="advertising_copy"
                />
            </li>
            <li>
                <Radio
                        label="Nein, dann benötige ich keinen Energieausweis für meine Immobilie. "
                        value=false id="np" name="radio" v-model="advertising_copy"
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
      advertising_copy: {
        get() {
          return this.$store.state.cart.advertising_copy
        },
        set(value) {
          this.$store.commit('SET_ADV', value)
        }
      }
    },
    methods: {}
  }
</script>

