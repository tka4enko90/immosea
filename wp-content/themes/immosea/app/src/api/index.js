import axios from 'axios'

// const hostName = window.location.origin
// const hostName = 'http://localhost:8888/immosea'
const hostName = 'http://immosea.markupus.tech'
const BASE_URL = `${hostName}/wp-json/rest_api/v1`

const headers = {
  'Content-Type': 'application/json',
  Accept: 'application/json',
};

const headersUploads = {
  'Content-Type': 'text/plain',
  Accept: '*/*',
};

const API = axios.create({
  baseURL: BASE_URL,
  headers
});

const APIUpload = axios.create({
  baseURL: BASE_URL,
  headers: headersUploads
});

const Product = {
  get: () => API.get(`${BASE_URL}/get_products/`)
};

const Order = {
  post: data => API.post(`${BASE_URL}/create_order/`, data),
  apply: data => API.post(`${BASE_URL}/apply_coupon/`, data)
};

const Media = {
  post: data => APIUpload.post(`${BASE_URL}/media/`, data),
};


export { Product, Order, Media };