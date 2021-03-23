<template>
    <StepWrap
            :title="title"
            :text="text"
            :buttonPrev="{...buttonPrev}"
            :buttonNext="{...buttonNext}"
    >
        <div class="choose-box__row">
            <div>
                <div class="choose-box">
                    <input type="radio" v-model="advertising_copy" value=false id="no">
                    <label for="no" class="choose-box__label">
                        <strong>Geld sparen</strong>
                        <p>Du brauchst keine Zeit in die oder Erstellung von Exposé und Werbetexten zu investieren.</p>
                        <span class="button button--outline">Eigene Texte verwenden</span>
                    </label>
                </div>
            </div>
            <div>
                <div class="choose-box">
                    <input type="radio" v-model="advertising_copy" value=true id="yes">
                    <label for="yes" class="choose-box__label">
                        <strong>Zeit sparen</strong>
                        <p>Leerstand kostet. Daher spielt dir eine kürzere Vermarktungs- zeit Geld in dein Sparschwein.</p>
                        <span class="button button--outline">Texte erstellen lassen</span>
                    </label>
                </div>
            </div>
        </div>
    </StepWrap>
</template>

<script>
  import StepWrap from '../Layout/StepWrap';

  export default {
    name: 'app-step4',
    components: {
      StepWrap
    },
    props: ['title', 'text', 'showPrice', 'buttonPrev', 'buttonNext'],
    data() {
      return {}
    },
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

