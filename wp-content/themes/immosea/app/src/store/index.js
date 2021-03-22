import Vue from 'vue';
import Vuex from 'vuex';
import { Product } from '../api';

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    products: [],
    cart: {},
    price: 0,
    type: 'flat',
    name_house: '',
    advertising_copy: false,
    expose: false,
    floor_plan: false,
    energy_certificate: false,
    energy_certificate_bg_house: false,
    virtual_staging: false,
    drone_footage: false,
    sellRent: '',
    year: ''
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

    SET_HOUSE_TYPE (state, payload) {
      state.type = payload
    },

    SET_SELL_RENT (state, payload) {
      state.sellRent = payload
    },

    SET_ADV (state, payload) {
      state.advertising_copy = payload
    },

    SET_YEAR (state, payload) {
      state.year = payload
    },
  },

  actions: {
    async fetchProducts ({ commit }) {
      const res = await Product.get()
      await commit('SET_PRODUCTS', res.data)
    }
  }
});