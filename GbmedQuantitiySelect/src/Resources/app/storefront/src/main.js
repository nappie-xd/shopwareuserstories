// Import all necessary Storefront plugins and scss files
import GbmedQuantitiySelect from './js/gbmed-quantitiy-select';

// Register them via the existing PluginManager
const PluginManager = window.PluginManager;
PluginManager.register('GbmedQuantitiySelect', GbmedQuantitiySelect, 'body');

//run plugins
document.addEventListener('DOMContentLoaded', () => {
    PluginManager.initializePlugins()
}, false);

// Necessary for the webpack hot module reloading server
if (module.hot) {
    module.hot.accept();
}
