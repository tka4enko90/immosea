import Vue from 'vue';
import Vuex from 'vuex';
import { Product } from '../api';

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    products: [],
    cart: {
      type: 'flat',
      advertising_copy: null,
      floor_plan: false,
      expose: false,
      energy_certificate: false,
      virtual_staging: false,
      drone_footage: false,
      photography: false,
      year: ''
    },
    price: 0,
    name_house: '',
    sellRent: '',
  },

  getters: {

  },

  mutations: {
    SET_PRODUCTS (state, products) {
      state.products = products
    },

    SET_NAME_HOUSE (state, payload) {
      state.name_house = payload
    },

    SET_SELL_RENT (state, payload) {
      state.sellRent = payload
    },

    SET_ADV (state, payload) {
      if (typeof payload === 'string' ) {
        payload === 'false'
          ? state.cart.advertising_copy = false
          : state.cart.advertising_copy = true
      } else {
        state.cart.advertising_copy = payload
      }
    },

    SET_CART_OPTIONS (state, payload) {
      state.cart = {
        ...state.cart,
        ...payload
      }
    }
  },

  actions: {
    async fetchProducts ({ commit }) {
      const res = await Product.get()
      await commit('SET_PRODUCTS', res.data)
    }
  }
});