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
                       label="Objekttitel" placeholder="Hier eintragen"/>
        </div>
        <div class="form__row">
            <label for="description" class="form__label">Objektbeschreibung</label>
            <textarea class="form__textarea form__textarea--small" id="description" cols="30" rows="10"
                      v-model="collectData.description"></textarea>
        </div>
        <div class="form__row">
            <label for="descriptionLocation" class="form__label">Lagebeschreibung</label>
            <textarea class="form__textarea form__textarea--small" id="descriptionLocation" cols="30" rows="10"
                      v-model="collectData.description_location"></textarea>
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
        <div class="form__row" v-if="type !== 'property'">
            <label for="rehabilitation" class="form__label">Vorgenommene Sanierungsmaßnahmen</label>
            <textarea class="form__textarea form__textarea--small" id="rehabilitation" cols="30" rows="10"
                      v-model="collectData.rehabilitation"></textarea>
        </div>
        <div class="form__row--separate" v-show="product_data.advertising_copy.price">
            <h3 class="text-center">Ich möchte die Werbetexte doch lieber erstellen lassen</h3>
            <div class="form-checkbox form-checkbox--custom" style="max-width: 320px; margin: 0 auto;">
                <input id="advertising_copy" type='checkbox' v-model="data.advertising_copy">
                <label for="advertising_copy">
                    <span>{{ product_data.advertising_copy.price }} EUR</span>
                    <strong>{{product_data.advertising_copy.title ? product_data.advertising_copy.title : 'Werbetexte' }}</strong>
                    {{ product_data.advertising_copy.description ? product_data.advertising_copy.description : 'Angepasst auf die gängigen Immobilienportale' }}
                </label>
            </div>
        </div>
    </StepWrap>
</template>

<script>
    import StepWrap from '../Layout/StepWrap';
    import InputText from '../Form/InputText';
    import {getDescriptionByFieldName, getPriceByFieldName, getTitleByFieldName} from '../../utils'


    export default {
        name: 'app-step9',
        components: {
            StepWrap,
            InputText
        },
        props: ['title', 'text', 'buttonPrev', 'buttonNext', 'showPrice'],
        data() {
            return {}
        },
        computed: {
            type() {
                return this.$store.state.cart.type
            },
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
                    this.$store.commit('SET_COLLECT_DATA', {value})
                }
            },
            product_data() {
                return {
                    advertising_copy: {
                        price: getPriceByFieldName(this.$store.state.products, 'advertising_copy'),
                        description: getDescriptionByFieldName(this.$store.state.products, 'advertising_copy'),
                        title: getTitleByFieldName(this.$store.state.products, 'advertising_copy'),
                    },
                }
            }
            // price() {
            //   return getPriceByFieldName(this.$store.state.products, 'advertising_copy')
            // }
        },
        methods: {}
    }
</script>

