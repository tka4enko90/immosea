<template>
    <StepWrap
            :title="title"
            :text="text"
            :buttonPrev="{...buttonPrev}"
            :buttonNext="{
                ...buttonNext,
                title: `Weiter zu ${method}`,
                click: onClick,
                sending: sending
            }"
            :showPrice="showPrice"
            :isLoading="isLoading"
    >
        <div class="heading">Deine ausgewählten Leistungen:</div>
        <div class="table">
            <div class="table__row table__row--head">
                <div>Artikelname</div>
                <div>Einzelpreis</div>
                <div>Menge</div>
                <div>Gesamt</div>
            </div>
            <div class="table__row" v-for="(item, index) in order.products" :key="index">
                <div>
                    <strong>{{ item.name }}</strong>
                    <span class="table__number">Nr. {{ item.sku }}</span>
                </div>
                <div>{{ item.price }} EUR</div>
                <div>{{ item.quantity }}</div>
                <div>{{ item.price * item.quantity }} EUR</div>
            </div>
        </div>
        <div class="table__coupon">
            <div>
                <InputText label="Dein Rabattcode" placeholder="Gustcheincode" v-model="coupon" />
                <button class="button button--small button--primary"
                        :class="{'button--disabled': isSending || !coupon || isCoupon}"
                        @click="apply"
                >
                    Anwenden
                    <div v-if="isSending" class="loader loader--small loader--position" />
                </button>
            </div>
            <div v-if="error" class="form__error">{{error}}</div>
        </div>
        <div class="table table--total">
            <div class="table__row">
                <div>Gesamt</div>
                <div class="table__price">
                    {{ order.total_price }} €
                    <span class="table__old-price" v-if="order.sub_total && order.amount > 0">{{ order.sub_total }} €</span>
                </div>
            </div>
            <div class="table__row" v-if="order.total_tax">
                <div>Tax</div>
                <div class="table__price">
                    {{ order.total_tax }} €
                </div>
            </div>
            <div class="table__row table__row--sale" v-if="order.amount > 0">
                <div>Rabatt</div>
                <div>{{ order.amount }} {{ order.amount_type === 'percent' ? ' %' : ' €'}}</div>
            </div>
        </div>
        <div class="heading">Zahlungsmöglichkeiten</div>
        <div class="table__method">
            <div v-if="order.payment_method && order.payment_method.paypal"  class="form-radio">
                <input type="radio" 
                       :id="order.payment_method.paypal.data.title"
                       :value="order.payment_method.paypal.data.title"
                       v-model="method">
                <label :for="order.payment_method.paypal.data.title">
                    <span v-if="order.payment_method.paypal.data.image"
                          v-html="order.payment_method.paypal.data.image"></span>
                    <span v-else>{{ order.payment_method.paypal.data.title }}</span>
                </label>
            </div>
            <div v-if="order.payment_method && order.payment_method.stripe_sofort"  class="form-radio">
                <input type="radio"
                       :id="order.payment_method.stripe_sofort.data.title"
                       :value="order.payment_method.stripe_sofort.data.title"
                       v-model="method">
                <label :for="order.payment_method.stripe_sofort.data.title">
                    <span v-if="order.payment_method.stripe_sofort.data.image"
                          v-html="order.payment_method.stripe_sofort.data.image"></span>
                    <span v-else>{{ order.payment_method.stripe_sofort.data.title }}</span>

                </label>
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
    data() {
      return {
        coupon: '',
        sending: false,
        method: 'PayPal'
    }},
    computed: {
      order() {
        return {
          order_id: this.$store.state.order.order_id,
          total_price: this.$store.state.order.total_price,
          sub_total: this.$store.state.order.sub_total,
          amount: this.$store.state.order.amount,
          products: this.$store.state.order.products,
          amount_type:  this.$store.state.order.amount_type,
          path: this.$store.state.order.result && this.$store.state.order.result.redirect || "/",
          total_tax: this.$store.state.order.total_tax,
          payment_method: this.$store.state.order.payment_method
        }
      },
      error() { return this.$store.state.error },
      isLoading() { return this.$store.state.isLoading },
      isSending() { return this.$store.state.isSending },
      isCoupon() { return this.$store.state.isCoupon },
    },
    methods: {
      apply() {
        this.$store.dispatch('applyCoupon', {
          coupon: this.coupon,
          order_id: this.order.order_id
        });
      },
      getPath(val) {
        return Object.keys(this.$store.state.order.payment_method).find(key =>
          this.$store.state.order.payment_method[key].data.title === val)
      },
      onClick() {
        this.sending = true
        window.location.href = this.$store.state.order.payment_method[this.getPath(this.method)].redirect
      }
    }
  }
</script>

