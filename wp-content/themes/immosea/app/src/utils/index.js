export const getPriceByFieldName = (arr, name) => {
  if (arr.length < 1)  return 0;
  let obj = arr.find(i => i.product_key === name);
  return obj ? obj.product_price : 0;
};

export const getDescriptionByFieldName = (arr, name) => {
  if (arr.length < 1)  return 0;
  let obj = arr.find(i => i.product_key === name);
  return obj ? obj.product_description : 0;
};

export const getTitleByFieldName = (arr, name) => {
  if (arr.length < 1)  return 0;
  let obj = arr.find(i => i.product_key === name);
  return obj ? obj.product_title : 0;
};
