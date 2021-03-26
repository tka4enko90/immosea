<template>
    <StepWrap
            :title="title"
            :text="text"
            :buttonPrev="{...buttonPrev}"
            :buttonNext="{...buttonNext}"
            :showPrice="showPrice"
    >
        <div class="form__row">
            <InputText v-model="collectData.title"
                       label="Objekttitel" placeholder="Type" />
        </div>
        <div class="form__row">
            <label for="description" class="form__label">Objektbeschreibung</label>
            <textarea class="form__textarea form__textarea--small" id="description" cols="30" rows="10"
                      v-model="collectData.description"></textarea>
        </div>
        <div class="form__row">
            <label for="descriptionLocation" class="form__label">Lagebeschreibung</label>
            <textarea class="form__textarea form__textarea--small" id="descriptionLocation" cols="30" rows="10"
                      v-model="collectData.descriptionLocation"></textarea>
        </div>
        <div class="form__row">
            <label for="leisure" class="form__label">Freizeit</label>
            <textarea class="form__textarea form__textarea--small" id="leisure" cols="30" rows="10"
                      v-model="collectData.leisure"></textarea>
        </div>
        <div class="form__row">
            <label for="others" class="form__label">Sonstiges</label>
            <textarea class="form__textarea form__textarea--small" id="others" cols="30" rows="10"
                      v-model="collectData.others"></textarea>
        </div>
        <div class="form__row--separate">
            <h3 class="text-center">Ich möchte die Werbetexte doch lieber erstellen lassen</h3>
            <div class="form-checkbox form-checkbox--custom" style="max-width: 320px; margin: 0 auto;">
                <input id="advertising_copy" type='checkbox' v-model="data.advertising_copy">
                <label for="advertising_copy">
                    <span>{{ price }} EUR</span>
                    <strong>Werbetexte</strong>
                    Angepasst auf die gängigen Immobilien portale
                </label>
            </div>
        </div>
    </StepWrap>
</template>

<script>
  import StepWrap from '../Layout/StepWrap';
  import InputText from '../Form/InputText';
  import { getPriceByFieldName } from '../../utils'


  export default {
    name: 'app-step9',
    components: {
      StepWrap,
      InputText
    },
    props: ['title', 'text', 'buttonPrev', 'buttonNext', 'showPrice'],
    data() {return {}},
    computed: {
      data: {
        get() {
          return this.$store.state.cart
        },
        set(value) {
          this.$store.commit('SET_CART_OPTIONS', value)
        }
      },
      collectData: {
        get() {
          return this.$store.state.collectData
        },
        set(value) {
          this.$store.commit('SET_COLLECT_DATA', { value })
        }
      },
      price() {
        return getPriceByFieldName(this.$store.state.products, 'advertising_copy')
      }
    },
    methods: {}
  }
</script>

