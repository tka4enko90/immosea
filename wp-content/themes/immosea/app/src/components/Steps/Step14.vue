<template>
    <StepWrap
            :title="title"
            :buttonPrev="{...buttonPrev}"
            :buttonNext="{
                ...buttonNext,
            }"
            :showPrice="showPrice"
    >
        step 14

    </StepWrap>
</template>

<script>
  import StepWrap from '../Layout/StepWrap';


  export default {
    name: 'app-step14',
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

