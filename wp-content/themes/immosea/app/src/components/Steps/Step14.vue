<template>
    <StepWrap
            :title="title"
            :text="text"
            :buttonPreOrder="{
                ...buttonPreOrder,
                click: handlerPreOrderClick,
                disabled: !$v.contactData.name.required
                        || !$v.contactData.last_name.required
                        || !$v.contactData.email.required
                        || !$v.contactData.phone.required
                        || !$v.contactData.email.email
                        || !$v.contactData.zip.required
                        || !$v.contactData.address.required
                // disabled: !cart.zustimmung_agb_datenschutz || !cart.zustimmung_ablauf_widerruf
            }"
            :buttonPrev="{...buttonPrev}"
            :buttonNext="{
                ...buttonNext,
                title: 'Bestellung überprüfen',
                click: handlerClick,
                disabled: !$v.contactData.name.required
                        || !$v.contactData.last_name.required
                        || !$v.contactData.email.required
                        || !$v.contactData.phone.required
                        || !$v.contactData.email.email
                        || !$v.contactData.zip.required
                        || !$v.contactData.address.required
                    }"
            :showPrice="showPrice"
    >
        <div class="form__row">
            <InputText v-model="contactData.name"
                       label="Name" placeholder="Hier eintragen" inline required
                       @blur="$v.contactData.name.$touch()"
                       :error="!$v.contactData.name.length"
                       errorMessage="At least 4 characters"
            />
        </div>
        <div class="form__row">
            <InputText v-model="contactData.last_name"
                       label="Vorname" placeholder="Hier eintragen" inline required
                       @blur="$v.contactData.last_name.$touch()"
                       :error="!$v.contactData.last_name.length"
                       errorMessage="At least 4 characters"
            />
        </div>
        <div class="form__row">
            <InputText v-model="contactData.address"
                       label="Anschrift"
                       @blur="$v.contactData.address.$touch()"
                       :error="!$v.contactData.address.length"
                       placeholder="Hier eintragen" inline required/>
        </div>
        <div class="form__row">
            <InputText v-model="contactData.zip"
                       label="PLZ, Ort"
                       @blur="$v.contactData.zip.$touch()"
                       :error="!$v.contactData.zip.length"
                       placeholder="Hier eintragen"
                       inline required/>
        </div>
        <div class="form__row">
            <InputText v-model="contactData.email"
                       label="E-Mail-Adresse" placeholder="Hier eintragen" inline type="email" required
                       @blur="$v.contactData.email.$touch()"
                       :error="!$v.contactData.email.email"
                       errorMessage="Bitte gültige E-Mail-Adresse eingeben"
            />
        </div>
        <div class="form__row">
            <InputText v-model="contactData.phone"
                       label="Telefonnummer" placeholder="Hier eintragen" inline required
                       @blur="$v.contactData.phone.$touch()"
                       :error="!$v.contactData.phone.length"
                       errorMessage="Bitte gültige Telefonnummer für Rückfragen eingeben"
                       type="number"
            />
        </div>
    </StepWrap>
</template>

<script>
    import StepWrap from '../Layout/StepWrap';
    import InputText from '../Form/InputText';
    import {required, email, minLength} from 'vuelidate/lib/validators';
    import {Order} from "../../api";


    export default {
        name: 'app-step14',
        components: {
            StepWrap, InputText
        },
        props: ['title', 'text', 'buttonPrev', 'buttonNext', 'buttonPreOrder', 'showPrice'],
        data() {
            return {}
        },
        computed: {
            contactData: {
                get() {
                    return this.$store.state.contactData
                },
                set(value) {
                    this.$store.commit('SET_CONTACT_DATA', {value})
                }
            }
        },
        validations: {
            contactData: {
                name: {required, length: minLength(1)},
                last_name: {required, length: minLength(1)},
                email: {required, email},
                phone: {required, length: minLength(6)},
                zip: {required, length: minLength(2)},
                address: {required, length: minLength(5)}
            },
        },
        methods: {
            handlerClick() {
                this.$store.dispatch('createOrder', {
                    cart: this.$store.state.cart,
                    collectData: this.$store.state.collectData,
                    contactData: this.$store.state.contactData
                });
                this.buttonNext.click()
            },
            async handlerPreOrderClick() {
                this.$store.commit('SET_IS_PRE_ORDER', true);
                this.sending = true;
                const res = await Order.post({
                    cart: this.$store.state.cart,
                    collectData: this.$store.state.collectData,
                    contactData: this.$store.state.contactData,
                    action: 'redirect',
                    pre_order: this.$store.state.preOrder
                });
                if (res.data && res.data.result === 'success') {
                    window.location.href = res.data.redirect
                }
                // this.buttonNext.click()
            },
            async onClick() {
                this.sending = true
                const res = await Order.post({
                    cart: this.$store.state.cart,
                    collectData: this.$store.state.collectData,
                    contactData: this.$store.state.contactData,
                    action: 'redirect'
                });
                if (res.data && res.data.result === 'success') {
                    window.location.href = res.data.redirect
                }
            }
        }
    }
</script>

