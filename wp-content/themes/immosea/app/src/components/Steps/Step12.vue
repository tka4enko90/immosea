<template>
    <StepWrap
            :title="title"
            :text="text"
            :buttonPrev="{...buttonPrev}"
            :buttonNext="{
                ...buttonNext,
            }"
            :showPrice="showPrice"
    >
        step 12

    </StepWrap>
</template>

<script>
  import StepWrap from '../Layout/StepWrap';


  export default {
    name: 'app-step12',
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

