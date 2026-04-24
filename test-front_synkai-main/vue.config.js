const { defineConfig } = require('@vue/cli-service')

module.exports = defineConfig({
  transpileDependencies: true,
  publicPath: '/',
  chainWebpack: (config) => {
    // Permitir importar PDFs desde `src/assets/**` (descarga de términos, etc.)
    config.module
      .rule('pdf')
      .test(/\.pdf$/i)
      .type('asset/resource')
      .set('generator', {
        filename: 'assets/[name].[contenthash:8][ext]',
      });
  },
  devServer: {
    proxy: {
      '/api': {
        target: process.env.VUE_APP_BACKEND_ORIGIN || 'http://127.0.0.1:8000',
        changeOrigin: true,
      },
    },
  },
})
