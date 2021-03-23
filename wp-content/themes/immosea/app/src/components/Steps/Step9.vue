<template>
    <StepWrap
            :title="title"
            :buttonPrev="{...buttonPrev}"
            :buttonNext="{
                ...buttonNext,
            }"
            :showPrice="showPrice"
    >
        step 9

    </StepWrap>
</template>

<script>
  import StepWrap from '../Layout/StepWrap';


  export default {
    name: 'app-step9',
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

