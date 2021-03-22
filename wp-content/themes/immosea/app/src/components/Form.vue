<template>
    <div class="form">
        <div class="form__holder">
            <div v-for="(item, index) in questions"
                 :key="index"
                 v-show="activeStep === index"
            >
                <component
                        :is="item.component"
                        :title="item.title"
                        :text="item.text"
                        :showPrice="item.price"
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

                <!--<div class="form__buttons">-->
                    <!--<button @click="showPrevScreen"-->
                            <!--v-show="buttonPrev.show"-->
                            <!--class="button button&#45;&#45;outline button&#45;&#45;small">-->
                        <!--{{ buttonPrev.title }}-->
                    <!--</button>-->
                    <!--<button @click="showNextScreen"-->
                            <!--class="button button&#45;&#45;primary button&#45;&#45;small">-->
                        <!--{{ buttonNext.title }}-->
                    <!--</button>-->
                <!--</div>-->
            </div>
        </div>
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
  import { questions } from '../Data/questions'

  export default {
    name: 'Form',
    components: {
      Step1, Step2, Step3,
      Step4, Step5, Step6,
      Step7, Step8, Step9,
      Step10, Step11, Step12,
      Step13, Step14
    },
    data() {
      return {
        buttonNext: {
          title: 'Next'
        },
        buttonPrev: {
          title: 'Prev',
          show: false
        },
        passedSteps: [],
        questions,
        activeStep: 0
      }
    },
    computed: {
      products() {
        return this.$store.state.products
      },
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
            condition: this.$store.state.type === 'house'
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
            condition: 'process'
          },
          {
            step: 6,
            condition: 'process'
          },
          {
            step: 7,
            condition: 'process'
          },
          {
            step: 8,
            condition: 'process'
          },
          {
            step: 9,
            condition: 'process'
          },
          {
            step: 10,
            condition: 'process'
          },
          {
            step: 11,
            condition: 'process'
          },
          {
            step: 12,
            condition: 'process'
          },
          {
            step: 13,
            condition: true
          }
        ]
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
        this.buttonPrev.show = true

        if (this.activeStep < this.questions.length - 1) {
          this.passedSteps.push(this.activeStep)
          this.activeStep = this.findNextScreen(this.activeStep)
        }

        if (this.activeStep === this.questions.length - 1) {
          this.buttonNext.title = 'Create Order'
        }
        window.scrollTo(0,0);
      },
      showPrevScreen() {
        this.activeStep = this.passedSteps[this.passedSteps.length - 1]
        this.buttonPrev.show = true
        this.buttonNext.title = 'Next'
        this.passedSteps.pop(this.passedSteps.length - 1)

        if (this.activeStep === 0) {
          this.buttonPrev.show = false
        }
        window.scrollTo(0,0);
      },
      findNextScreen(index) {
        return this.condition.find(i => i.step > index && i.condition).step
      }
    }
  }
</script>
<style lang="scss" scoped></style>

