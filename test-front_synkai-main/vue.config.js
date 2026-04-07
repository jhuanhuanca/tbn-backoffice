const { defineConfig } = require('@vue/cli-service')

module.exports = defineConfig({
  transpileDependencies: true,
  publicPath: './',
  devServer: {
    proxy: {
      '/api': {
        target: process.env.VUE_APP_BACKEND_ORIGIN || 'http://127.0.0.1:8000',
        changeOrigin: true,
      },
    },
  },
})
