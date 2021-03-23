<template>
    <StepWrap
            :title="title"
            :buttonPrev="{
                ...buttonPrev
            }"
            :buttonNext="{
                ...buttonNext,
            }"
            :showPrice="showPrice"
    >
        step 7

    </StepWrap>
</template>

<script>
  import StepWrap from '../Layout/StepWrap';


  export default {
    name: 'app-step7',
    components: {
      StepWrap
    },
    props: ['title', 'text', 'buttonPrev', 'buttonNext', 'showPrice'],
    data() {return {}},
    computed: {

    },
    methods: {

    }
  }
</script>

