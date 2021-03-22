<template>
    <div class="step">
        <div class="step__title">
            <h1>{{ title }}</h1>
            <div class="step__info" v-if="text">
                {{ text }}
            </div>
        </div>
        <div class="step__content">
            <div v-if="showPrice" class="price-box">
                Price box
            </div>
            <div class="step__holder">
                <slot></slot>
            </div>

            <div class="step__buttons">
                <button @click="buttonPrev.click"
                    v-show="buttonPrev.show"
                    class="button button--outline button--small"
                >
                    {{ buttonPrev.title }}
                </button>
                <button @click="buttonNext.click"
                        class="button button--primary button--small"
                        :class="{'button--disabled': buttonNext.disabled}"
                >
                    {{ buttonNext.title }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>

  export default {
    name: 'app-step-wrap',
    components: {},
    props: ['title', 'text', 'showPrice', 'buttonPrev', 'buttonNext' ],
    computed: {},
    methods: {},
    create: {}
  }
</script>

