import Vue from 'vue';
import Vuex from 'vuex';
import { Product } from '../api';
import { getPriceByFieldName } from '../utils'

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    products: [],
    cart: {
      type: 'flat',
      year: '',
      uploads: [],
      graphics3d: false,
      advertising_copy: null,
      floor_plan: false,
      expose: false,
      energy_certificate: false,
      photography: false,

      virtual_staging: false,
      drone_footage: false,
    },
    name_house: '',
    sellRent: '',
    collectData: {
      monumentProtection: false,
      ensembleProtection: false,
      demolitionObject: false
    }
  },

  getters: {
    price: state => {
      let advertising = state.cart.advertising_copy ? getPriceByFieldName(state.products, 'advertising_copy') : 0
      let expose      = state.cart.expose ? getPriceByFieldName(state.products, 'expose') : 0
      let certificate = state.cart.energy_certificate
                                  ? state.cart.type === 'house' && state.cart.year < 1979
                                    ? getPriceByFieldName(state.products, 'energy_certificate_bg_house')
                                    : getPriceByFieldName(state.products, 'energy_certificate')
                                  : 0
      let photography = state.cart.photography
                                  ? getPriceByFieldName(state.products, `photography_${state.cart.type}`)
                                  : 0
      let floor_plan  = state.cart.floor_plan && state.cart.uploads.length > 0
                                  ? getPriceByFieldName(state.products, 'floor_plan')
                                  : 0

      // let graphics3d  = state.graphics3d && state.cart.uploads.length > 0
      //                             ? getPriceByFieldName(state.products, 'floor_plan')
      //                             : 0



      return +advertising + +expose + +certificate + +photography + +floor_plan
    }
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
    },

    SET_COLLECT_DATA (state, payload) {
      state.collectData = {
        ...state.collectData,
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