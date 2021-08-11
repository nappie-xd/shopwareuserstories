import QuantityFieldPlugin from './script/quantity-field.plugin'

console.log(document.querySelectorAll('[data-quantity-field]'))

window.PluginManager.register('QuantityField', QuantityFieldPlugin, '[data-quantity-field]')