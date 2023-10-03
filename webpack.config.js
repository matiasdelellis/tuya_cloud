const path = require('path')
const webpackConfig = require('@nextcloud/webpack-vue-config')

webpackConfig.entry = {
    dashboard: { import: path.join(__dirname, 'src', 'dashboard.js') },
    settings: { import: path.join(__dirname, 'src', 'settings.js') }
}

module.exports = webpackConfig

