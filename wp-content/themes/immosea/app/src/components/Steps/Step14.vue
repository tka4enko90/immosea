<template>
    <StepWrap
            :title="title"
            :text="text"
            :buttonPrev="{...buttonPrev}"
            :buttonNext="{
                ...buttonNext,
                title: 'Create Order',
                click: handlerClick,
                disabled: !$v.contactData.name.required
                        || !$v.contactData.last_name.required
                        || !$v.contactData.email.required
                        || !$v.contactData.phone.required
                        || !$v.contactData.email.email
                    }"
            :showPrice="showPrice"
    >
        <div class="form__row">
            <InputText v-model="contactData.name"
                       label="Name" placeholder="Type" inline required
                       @blur="$v.contactData.name.$touch()"
                       :error="!$v.contactData.name.length"
                       errorMessage="At least 4 characters"
            />
        </div>
        <div class="form__row">
            <InputText v-model="contactData.last_name"
                       label="Vorname" placeholder="Type" inline required
                       @blur="$v.contactData.last_name.$touch()"
                       :error="!$v.contactData.last_name.length"
                       errorMessage="At least 4 characters"
            />
        </div>
        <div class="form__row">
            <InputText v-model="contactData.address" label="Anschrift" placeholder="Type" inline />
        </div>
        <div class="form__row">
            <InputText v-model="contactData.zip" label="PLZ, Ort" placeholder="Type" inline />
        </div>
        <div class="form__row">
            <InputText v-model="contactData.email"
                       label="E-Mail-Adresse" placeholder="Type" inline type="email" required
                       @blur="$v.contactData.email.$touch()"
                       :error="!$v.contactData.email.email"
                       errorMessage="Must be an email"
            />
        </div>
        <div class="form__row">
            <InputText v-model="contactData.phone"
                       label="Telefonnummer" placeholder="Type" inline required
                       @blur="$v.contactData.phone.$touch()"
                       :error="!$v.contactData.phone.length"
                       errorMessage="Required"
            />
        </div>
    </StepWrap>
</template>

<script>
  import StepWrap from '../Layout/StepWrap';
  import InputText from '../Form/InputText';
  import { required, email, minLength } from 'vuelidate/lib/validators';


  export default {
    name: 'app-step14',
    components: {
      StepWrap, InputText
    },
    props: ['title', 'text', 'buttonPrev', 'buttonNext', 'showPrice'],
    data() {return {}},
    computed: {
      contactData: {
        get() {
          return this.$store.state.contactData
        },
        set(value) {
          this.$store.commit('SET_CONTACT_DATA', { value })
        }
      }
    },
    validations: {
      contactData: {
        name: { required, length: minLength(4) },
        last_name: { required, length: minLength(4) },
        email: { required, email },
        phone: { required, length: minLength(6) }
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
      }
    }
  }
</script>

