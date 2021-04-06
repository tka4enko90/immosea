<template>
    <div class="form">
        <div class="form__holder">
            <div v-for="(item, index) in filterSteps"
                 :key="index"
                 v-show="activeStep === index"
            >
                <component
                        :is="questions[item.step].component"
                        :title="questions[item.step].title"
                        :text="questions[item.step].text"
                        :showPrice="questions[item.step].showPrice"
                        :buttonPrev="{
                            title: buttonPrev.title,
                            click: showPrevScreen,
                            show: buttonPrev.show
                        }"
                        :buttonNext="{
                            title: buttonNext.title,
                            click: showNextScreen
                        }"
                ></component>
            </div>
        </div>
        <!--{{products}}-->
        <!--{{order}}-->
        <!--{{cart}} <br/>-->
        <!--{{collectData}}-->
    </div>
</template>

<script>
  import Step1 from './Steps/Step1'
  import Step2 from './Steps/Step2'
  import Step3 from './Steps/Step3'
  import Step4 from './Steps/Step4'
  import Step5 from './Steps/Step5'
  import Step6 from './Steps/Step6'
  import Step7 from './Steps/Step7'
  import Step8 from './Steps/Step8'
  import Step9 from './Steps/Step9'
  import Step10 from './Steps/Step10'
  import Step11 from './Steps/Step11'
  import Step12 from './Steps/Step12'
  import Step13 from './Steps/Step13'
  import Step14 from './Steps/Step14'
  import Step15 from './Steps/Step15'
  import { questions } from '../Data/questions'



  export default {
    name: 'Form',
    components: {
      Step1, Step2, Step3,
      Step4, Step5, Step6,
      Step7, Step8, Step9,
      Step10, Step11, Step12,
      Step13, Step14, Step15
    },
    data() {
      return {
        buttonNext: {
          title: 'Weiter'
        },
        buttonPrev: {
          title: 'Zurück',
          show: false
        },
        passedSteps: [],
        questions,
        activeStep: 0
      }
    },
    computed: {
      collectData() { return this.$store.state.collectData },
      cart() { return this.$store.state.cart },
      products() { return this.$store.state.products },
      order() { return this.$store.state.order },
      condition() {
        return [
          {
            step: 0,
            condition: true
          },
          {
            step: 1,
            condition: true
          },
          {
            step: 2,
            condition: this.$store.state.cart.type === 'house'
          },
          {
            step: 3,
            condition: true
          },
          {
            step: 4,
            condition: true
          },
          {
            step: 5,
            condition: this.$store.state.cart.advertising_copy || this.$store.state.cart.expose || this.$store.state.cart.energy_certificate
          },
          {
            step: 6,
            condition: this.$store.state.cart.expose && !this.$store.state.cart.energy_certificate &&
              !this.$store.state.collectData.monument_protection
          },
          {
            step: 7,
            condition: this.$store.state.cart.energy_certificate && (!this.$store.state.collectData.monument_protection
              || !this.$store.state.collectData.ensemble_protection || !this.$store.state.collectData.demolition_object)
          },
          {
            step: 8,
            condition: this.$store.state.cart.expose && !this.$store.state.cart.advertising_copy
          },
          {
            step: 9,
            condition: this.$store.state.cart.advertising_copy
          },
          {
            step: 10,
            condition: this.$store.state.cart.expose && this.$store.state.cart.photography && this.$store.state.cart.drone_footage
          },
          {
            step: 11,
            condition: this.$store.state.cart.expose
          },
          {
            step: 12,
            condition: this.$store.state.cart.floor_plan
          },
          {
            step: 13,
            condition: true
          },
          {
            step: 14,
            condition: true
          }
        ]
      },
      filterSteps() {
        console.log(this.condition.filter(i => i.condition));
        return this.condition.filter(i => i.condition)
      }
    },
    created() {
      this.fetchData()
    },
    methods: {
      fetchData() {
        this.$store.dispatch('fetchProducts')
      },
      showNextScreen() {
        if (this.activeStep < this.filterSteps.length - 1) {
          this.activeStep++
        }

        // if (this.activeStep < this.questions.length - 1) {
        //   this.passedSteps.push(this.activeStep)
        //   this.activeStep = this.findNextScreen(this.activeStep)
        //
        //   // this.$cookies.set('passedSteps', this.passedSteps)
        //   // this.$cookies.set('activeStep', this.activeStep)
        // }

        window.scrollTo(0,0);
      },
      showPrevScreen() {
        // this.activeStep = this.passedSteps[this.passedSteps.length - 1]
        this.buttonPrev.show = true
        // this.buttonNext.title = 'Weiter'
        // this.passedSteps.pop(this.passedSteps.length - 1)

        // this.$cookies.set('passedSteps', JSON.stringify(this.passedSteps))
        // this.$cookies.set('activeStep', this.activeStep)

        if (this.activeStep !== 0) {
          this.activeStep--
        } else {
          this.buttonPrev.show = false
        }
        window.scrollTo(0,0);
      },
      // findNextScreen(index) {
      //   return this.condition.find(i => i.step > index && i.condition).step
      // }
    }
  }
</script>
<style lang="scss" scoped></style>

