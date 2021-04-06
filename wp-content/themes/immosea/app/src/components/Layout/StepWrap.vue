<template>
    <div class="step" :class="{'step--price': showPrice}">
        <div class="step__title">
            <h1>{{ title }}</h1>
            <div class="step__info" v-if="text">
                {{ text }}
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
                <button @click="buttonNext.click"
                        class="button button--primary"
                        :class="{'button--disabled': buttonNext.disabled}"
                >
                    {{ buttonNext.title }}
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
    props: ['title', 'text', 'showPrice', 'buttonPrev', 'buttonNext', 'isLoading' ],
    computed: {
      ...mapGetters([
        'price'
      ]),
    },
    methods: {},
    create: {}
  }
</script>

