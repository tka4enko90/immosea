import Vue from 'vue';
import Vuex from 'vuex';
import { Product, Order } from '../api';
import { getPriceByFieldName } from '../utils';

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    products: [],
    cart: {
      type: 'flat',
      year: '',
      image: '',
      uploads_images: [],
      surcharge_3d_floor: false,
      advertising_copy: null,
      floor_plan: false,
      expose: false,
      energy_certificate: false,
      photography: false,
      drone_footage: false,
      further_floor_plan: false,
      mailaddress: false,
      online_inserat: false,

      virtual_staging: false,

      zustimmung_agb_datenschutz: false,
      zustimmung_ablauf_widerruf: false
    },
    collectData: {
      name_house: '',
      sell_rent: '',
      monument_protection: false,
      ensemble_protection: false,
      demolition_object: false,
      uploads_docs: [],
      uploads: []
    },
    contactData: {
      name: '',
      last_name:'',
    },
    order: {
      amount: 0
    },
    coupon: {
      amount: 0
    },
    preOrder : false,
    isLoading: false,
    isSending: false,
    error: '',
    isCoupon: false,
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

      let floor_plan  = state.cart.floor_plan
                                  ? getPriceByFieldName(state.products, 'floor_plan')
                                  : 0

      let surcharge_3d_floor  = state.cart.surcharge_3d_floor
                                  ? getPriceByFieldName(state.products, 'surcharge_3d_floor') * (state.cart.uploads_images.length + 1)
                                  : 0

      let further_floor_plan = state.cart.uploads_images.length > 0
                                  ? getPriceByFieldName(state.products, 'further_floor_plan') * state.cart.uploads_images.length
                                  : 0

      let drone_footage  = state.cart.drone_footage
                              ? getPriceByFieldName(state.products, 'drone_footage')
                              : 0

      let mailaddress  = state.cart.drone_footage
                          ? getPriceByFieldName(state.products, 'mailaddress')
                          : 0

      let online_inserat = state.cart.drone_footage
                              ? getPriceByFieldName(state.products, 'online_inserat')
                              : 0

      return +advertising + +expose + +certificate + +photography
             + +floor_plan + +surcharge_3d_floor + +further_floor_plan
             + +drone_footage + +online_inserat + +mailaddress
    }
  },

  mutations: {
    SET_PRODUCTS (state, products) {
      state.products = products
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

    SET_CERTIFICATE (state, payload) {
      if (typeof payload === 'string' ) {
        payload === 'false'
          ? state.cart.energy_certificate = false
          : state.cart.energy_certificate = true
      } else {
        state.cart.energy_certificate = payload
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
    },

    SET_CONTACT_DATA (state, payload) {
      state.contactData = {
        ...state.contactData,
        ...payload
      }
    },

    SET_ORDER (state, payload) {
      state.order = payload
    },

    SET_COUPON (state, payload) {
      state.coupon = payload
    },


    SET_LOADING (state, payload) {
      state.isLoading = payload
    },

    SET_SENDING (state, payload) {
      state.isSending = payload
    },

    SET_ERROR (state, payload) {
      state.error = payload
    },

    SET_IS_COUPON (state, payload) {
      state.isCoupon = payload
    },
    SET_IS_PRE_ORDER (state, payload) {
      state.preOrder = payload
    },
  },

  actions: {
    setDataFromCookies ({ commit }) {
      if (window.$cookies.get('collectData')) {
        commit('SET_COLLECT_DATA', window.$cookies.get('collectData'))
      }
      if (window.$cookies.get('cart')) {
        commit('SET_CART_OPTIONS', window.$cookies.get('cart'))
      }
      if (window.$cookies.get('contactData')) {
        commit('SET_CONTACT_DATA', window.$cookies.get('contactData'))
      }
    },

    async fetchProducts ({ commit }) {
      try {
        const res = await Product.get()
        await commit('SET_PRODUCTS', res.data)
      }
      catch (e) {
        console.error(e);
      }
    },

    async createOrder ({ commit }, data) {
      try {
        await commit('SET_LOADING', true)
        const res = await Order.post(data)
        await commit('SET_ORDER', res.data)
        await commit('SET_LOADING', false)
      }
      catch (e) {
        console.error(e);
        await commit('SET_LOADING', false)
      }
    },

    async applyCoupon ({ commit }, data) {
      try {
        await commit('SET_SENDING', true)
        const res = await Order.apply(data)

        if (res.data && res.data.status === 404) {
          await commit('SET_ERROR', 'Der eingegebene Gutscheincode ist nicht gültig.')
        } else {
          await commit('SET_ERROR', '')
          await commit('SET_ORDER', res.data)
          await commit('SET_IS_COUPON', true)
        }

        await commit('SET_SENDING', false)
      }
      catch (e) {
        console.error(e);
        await commit('SET_SENDING', false)
      }
    },
  }
});
