import ProductHoverPlugin from './script/product-hover.plugin'

const PluginManager = window.PluginManager;
PluginManager.register('ProductHover', ProductHoverPlugin, '[data-listing]')
