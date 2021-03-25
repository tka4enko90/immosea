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
        <div class="form__row">
            <label class="form__label">Ausstattung</label>
            <v-select :options="options" v-model="collectData.furnishing" placeholder="Select" multiple />
        </div>
        <div class="form__row">
            <label for="furtherEquipment" class="form__label">Weitere Ausstattung</label>
            <textarea class="form__textarea form__textarea--small" id="furtherEquipment" cols="30" rows="10"
                      v-model="collectData.furtherEquipment"></textarea>
        </div>
        <div class="form__row">
            <label for="rehabilitation" class="form__label">Vorgenommene Sanierungsmaßnahmen</label>
            <textarea class="form__textarea form__textarea--small" id="rehabilitation" cols="30" rows="10"
                      v-model="collectData.rehabilitation"></textarea>
        </div>
        <div class="form__row">
            <label class="form__label">Bodenbeläge</label>
            <v-select :options="options2" v-model="collectData.floorCoverings" placeholder="Select" multiple />
        </div>
        <div class="form__row">
            <label for="keyPoints" class="form__label">Beschreibung (Stichpunkte)</label>
            <textarea class="form__textarea form__textarea--small" id="keyPoints" cols="30" rows="10"
                      v-model="collectData.keyPoints"></textarea>
        </div>
    </StepWrap>
</template>

<script>
  import StepWrap from '../Layout/StepWrap';
  import vSelect from 'vue-select';
  import { Ausstattung, Bodenbelage } from '../../Data/options';


  export default {
    name: 'app-step10',
    components: {
      StepWrap,
      vSelect
    },
    props: ['title', 'text', 'buttonPrev', 'buttonNext', 'showPrice'],
    data() {return {
      options: Ausstattung,
      options2: Bodenbelage
    }},
    computed: {
      collectData: {
        get() {
          return this.$store.state.collectData
        },
        set(value) {
          this.$store.commit('SET_COLLECT_DATA', { value })
        }
      },
    },
    methods: {}
  }
</script>

