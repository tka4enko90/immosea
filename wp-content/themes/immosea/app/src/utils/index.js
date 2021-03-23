export const getPriceByFieldName = (arr, name) => {
  if (arr.length < 1) return 0

  return arr.find(i => i.product_key === name).product_price
}