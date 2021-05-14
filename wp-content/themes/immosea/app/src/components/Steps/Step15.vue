<template>
    <StepWrap
            :title="title"
            :text="text"
            :buttonPrev="{
                ...buttonPrev,
                click: onClickBack,
            }"
            :buttonNext="{
                ...buttonNext,
                title: `Zahlungspflichtig bestellen ${method}`,
                click: onClick,
                sending: sending,
                disabled: !cart.zustimmung_agb_datenschutz || !cart.zustimmung_ablauf_widerruf
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
            <div v-if="!order.coupon">
                <div class="table__coupon-form">
                    <InputText label="Dein Rabattcode" placeholder="Gustcheincode" v-model="couponInput" />
                    <button class="button button--small button--primary"
                            :class="{'button--disabled': isSending || !couponInput || isCoupon}"
                            @click="apply"
                    >
                        Anwenden
                        <div v-if="isSending" class="loader loader--small loader--position" />
                    </button>
                </div>
                <div v-if="error" class="form__error">{{error}}</div>
            </div>
            <div v-else>
                <div class="form__label">Dein Rabattcode</div>
                <div class="table__coupon--apply">{{ order.coupon }}</div>
            </div>
        </div>
        <div class="table table--total">

            <div class="table__row">
                <div>Gesamtsumme inkl. MwSt.</div>
                <div class="table__price">
                    {{ order.total_price }} €
                    <span class="table__old-price" v-if="order.sub_total && order.amount > 0">{{ order.sub_total }} €</span>
                </div>
            </div>
            <div class="table__row" v-if="order.total_tax">
                <div>Darin enthaltene MwSt.</div>
                <div class="table__price">
                    {{ order.total_tax }} €
                </div>
            </div>
            <div class="table__row table__row--sale" v-if="order.amount > 0">
                <div>Rabatt</div>
                <div class="table__price">{{ order.amount }} {{ order.amount_type === 'percent' ? ' %' : ' €'}}</div>
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
            <div v-if="order.payment_method && order.payment_method.german_market_purchase_on_account"  class="form-radio">
                <input type="radio"
                       :id="order.payment_method.german_market_purchase_on_account.data.title"
                       :value="order.payment_method.german_market_purchase_on_account.data.title"
                       v-model="method">
                <label :for="order.payment_method.german_market_purchase_on_account.data.title">
                    <span v-if="order.payment_method.german_market_purchase_on_account.data.image"
                          v-html="order.payment_method.german_market_purchase_on_account.data.image"></span>
                    <span v-else>{{ order.payment_method.german_market_purchase_on_account.data.title }}</span>

                </label>
            </div>
        </div>

        <div class="form__row">
            <div class="form-checkbox form-checkbox--small">
                <input id="zustimmung_agb_datenschutz" type='checkbox' v-model="cart.zustimmung_agb_datenschutz">
                <label for="zustimmung_agb_datenschutz">
                    Ich akzeptiere die <a href="#">AGB</a>. Ich habe die <a href="#">Datenschutzerklärung</a> zur Kenntnis genommen. Ich stimme zu, dass meine Angaben und Daten zur Beantwortung meines Auftrags elektronisch erhoben und gespeichert werden.
                </label>
            </div>
        </div>
        <div class="form__row">
            <div class="form-checkbox form-checkbox--small">
                <input id="zustimmung_ablauf_widerruf" type='checkbox' v-model="cart.zustimmung_ablauf_widerruf">
                <label for="zustimmung_ablauf_widerruf">
                    Ich verlange ausdrücklich und stimme gleichzeitig zu, dass Sie mit der in Auftrag gegebenen Dienstleistung vor Ablauf der Widerrufsfrist beginnen. Ich weiß, dass mein Widerrufsrecht bei vollständiger Erfüllung des Vertrages erlischt.
                </label>
            </div>
        </div>
    </StepWrap>
</template>

<script>
  import StepWrap from '../Layout/StepWrap';
  import InputText from '../Form/InputText';
  import { Order } from '../../api';

  export default {
    name: 'app-step15',
    components: {
      StepWrap, InputText
    },
    props: ['title', 'text', 'buttonPrev', 'buttonNext', 'showPrice'],
    data() {
      return {
        couponInput: '',
        sending: false,
        method: 'PayPal'
    }},
    computed: {
      order() {
        return {
          order_id: this.$store.state.order.order_id,
          total_price: this.$store.state.order.total_price,
          products: this.$store.state.order.products,
          path: this.$store.state.order.result && this.$store.state.order.result.redirect || "/",
          total_tax: this.$store.state.order.total_tax,
          payment_method: this.$store.state.order.payment_method,
          amount: this.$store.state.order.amount,
          sub_total: this.$store.state.order.sub_total,
          amount_type:  this.$store.state.order.amount_type,
          coupon: this.$store.state.order.coupon
        }
      },
      error() { return this.$store.state.error },
      isLoading() { return this.$store.state.isLoading },
      isSending() { return this.$store.state.isSending },
      isCoupon() { return this.$store.state.isCoupon },
      cart: {
        get() { return this.$store.state.cart },
        set(value) {
          this.$store.commit('SET_CART_OPTIONS', { value })
        }
      }
    },
    methods: {
      apply() {
        this.$store.dispatch('applyCoupon', {
          coupon: this.couponInput,
          order_id: this.order.order_id
        });
      },
      getPath(val) {
        return Object.keys(this.$store.state.order.payment_method).find(key =>
          this.$store.state.order.payment_method[key].data.title === val)
      },
      onClickBack() {
        this.couponInput = ''
        this.$store.commit('SET_IS_COUPON', false)
        this.$store.commit('SET_COUPON', {})
        this.buttonPrev.click()
      },
      async onClick() {
        this.sending = true
        const res = await Order.post({
          cart: this.$store.state.cart,
          collectData: this.$store.state.collectData,
          contactData: this.$store.state.contactData,
          action: 'redirect',
          payment_method: this.getPath(this.method)
        })

        if (res.data && res.data.result === 'success') {
          window.location.href = res.data.redirect
        }
      }
    }
  }
</script>

