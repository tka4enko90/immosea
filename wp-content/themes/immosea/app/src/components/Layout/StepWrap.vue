<template>
    <div class="step" :class="{'step--price': showPrice, 'step-with-pre-order': preOrderTemplate}">
        <div class="step__title">
            <h1>{{ title }}</h1>
            <div class="step__info" v-if="text">
                <span v-html="text"></span>
            </div>
        </div>
        <div class="step__content">
            <div v-if="showPrice" class="step__top">
                <div>
                    <strong>Mein Marketingpaket</strong>
                    inkl. MwSt.
                </div>
                <div class="step__price-box">
                    {{ this.price }} EUR
                </div>
            </div>
            <div class="step__holder">
                <div v-if="isLoading" class="loader"></div>
                <slot v-else></slot>
            </div>

            <div class="step__buttons">
                <button @click="buttonPrev.click"
                    v-show="buttonPrev.show"
                    class="button button--back"
                >
                    {{ buttonPrev.title }}
                </button>
                <button
                        v-if="buttonPreOrder"
                        @click="buttonPreOrder.click"
                        class="button button--outline button--small header__button"
                        :class="{'button--disabled': buttonPreOrder.disabled || buttonPreOrder.sending}"
                >
                    {{ buttonPreOrder.title }}
                </button>
                <button @click="buttonNext.click"
                        class="button button--primary"
                        :class="{'button--disabled': buttonNext.disabled || buttonNext.sending}"
                >
                    {{ buttonNext.title }}
                    <div v-if="buttonNext.sending"
                         class="loader loader--small loader--position"
                    />
                </button>
            </div>
        </div>
    </div>
</template>

<script>
  import { mapGetters } from 'vuex'


  export default {
    name: 'app-step-wrap',
    components: {},
    props: ['title', 'text', 'showPrice', 'buttonPrev', 'buttonNext', 'buttonPreOrder', 'isLoading' , 'preOrderTemplate'],
    computed: {

      ...mapGetters([
        'price'
      ]),
    },
    methods: {

    },
    create: {

    }
  }
</script>

