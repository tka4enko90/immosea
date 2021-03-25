module.exports = {
  chainWebpack: config => {
    config.performance
      .maxEntrypointSize(800000)
      .maxAssetSize(800000)
  }
}