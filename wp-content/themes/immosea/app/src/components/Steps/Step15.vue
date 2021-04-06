<template>
    <StepWrap
            :title="title"
            :text="text"
            :buttonPrev="{...buttonPrev}"
            :buttonNext="{
                ...buttonNext,}"
            :showPrice="showPrice"
            :isLoading="isLoading"
    >
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
                    Nr. xxx
                </div>
                <div>{{ item.price }} EUR</div>
                <div>{{ item.quantity }}</div>
                <div>{{ item.price * item.quantity }} EUR</div>
            </div>
        </div>
        <div class="table__coupon">
            <InputText label="Objekttitel" placeholder="Gustcheincode" v-model="coupon" />
            <button class="button button--small button--primary"
                    :class="{'button--disabled': isSending}"
                    @click="apply"
            >
                Apply
                <div v-if="isSending" class="loader loader--small loader--position" />
            </button>
        </div>
        <div class="table table--total">
            <div class="table__row">
                <div>Gesamt</div>
                <div class="table__price">
                    {{ order.total_price }} €
                    <span class="table__old-price" v-if="order.sub_total && order.amount > 0">{{ order.sub_total }} €</span>
                </div>
            </div>
            <div class="table__row table__row--sale" v-if="order.amount > 0">
                <div>Sale</div>
                <div>{{ order.amount }} %</div>
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
        coupon: ''
    }},
    computed: {
      order() {
        return {
          order_id: this.$store.state.order.order_id,
          total_price: this.$store.state.order.total_price,
          sub_total: this.$store.state.order.sub_total,
          amount: this.$store.state.order.amount,
          products: this.$store.state.order.products,
        }
      },
      isLoading() { return this.$store.state.isLoading },
      isSending() { return this.$store.state.isSending },
    },
    methods: {
      apply() {
        this.$store.dispatch('applyCoupon', {
          coupon: this.coupon,
          order_id: this.order.order_id
        });
      }
    }
  }
</script>

