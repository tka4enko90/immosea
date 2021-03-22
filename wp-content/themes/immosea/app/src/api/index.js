import axios from 'axios'

const BASE_URL = 'http://immosea.markupus.tech/wp-json/rest_api/v1';
const headers = {
  'Content-Type': 'application/json',
  Accept: 'application/json',
};

const API = axios.create({
  baseURL: BASE_URL,
  headers
});

const Product = {
  get: () => API.get(`${BASE_URL}/get_products/`),
  create: data => API.get(`${BASE_URL}/create_order/`, data)
};

export { Product };