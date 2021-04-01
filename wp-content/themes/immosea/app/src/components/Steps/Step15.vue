<template>
    <StepWrap
            :title="title"
            :text="text"
            :buttonPrev="{...buttonPrev}"
            :buttonNext="{
                ...buttonNext,}"
            :showPrice="showPrice"
    >
        <div class="table">
            <div class="table__row table__row--head">
                <div>Artikelname</div>
                <div>Einzelpreis</div>
                <div>Menge</div>
                <div>Gesamt</div>
            </div>
            <div class="table__row">
                <div>
                    <strong>Werbetext Erstellung</strong>
                    Nr. xxx
                </div>
                <div>95 EUR</div>
                <div>1</div>
                <div>95 EUR</div>
            </div>
            <div class="table__row">
                <div>
                    <strong>Werbetext Erstellung</strong>
                    Nr. xxx
                </div>
                <div>95 EUR</div>
                <div>1</div>
                <div>95 EUR</div>
            </div>
            <div class="table__row">
                <div>
                    <strong>Werbetext Erstellung</strong>
                    Nr. xxx
                </div>
                <div>95 EUR</div>
                <div>1</div>
                <div>95 EUR</div>
            </div>
        </div>
        <div class="table__coupon">
            <div class="form__row">
                <InputText label="Objekttitel" placeholder="Gustcheincode" />
            </div>
        </div>
        <div class="table table--total">
            <div class="table__row">
                <div>Gesamt</div>
                <div class="table__price">385 €</div>
            </div>
            <div class="table__row table__row--sale">
                <div>enthaltene MwSt. 19%</div>
                <div>61 €</div>
            </div>
        </div>
    </StepWrap>
</template>

<script>
  import StepWrap from '../Layout/StepWrap';
  import InputText from '../Form/InputText';


  export default {
    name: 'app-step15',
    components: {
      StepWrap, InputText
    },
    props: ['title', 'text', 'buttonPrev', 'buttonNext', 'showPrice'],
    data() {return {}},
    computed: {

    },
    methods: {

    }
  }
</script>

