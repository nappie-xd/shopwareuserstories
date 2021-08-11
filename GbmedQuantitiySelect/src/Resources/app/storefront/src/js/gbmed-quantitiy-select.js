import Plugin from 'src/plugin-system/plugin.class';
//import Iterator from 'src/helper/iterator.helper';

export default class GbmedQuantitiySelect extends Plugin {
    static options = {
        subscribes: [{
            plugin: null,
            eventName: null,
            elementSelector: '.custom-select.product-detail-quantity-select',
            isSmall: false
        }, {
            plugin: null,
            eventName: null,
            elementSelector: '.custom-select.quantity-select',
            isSmall: true,
            event: {
                plugin: 'FormAutoSubmit',
                callback: '_onChange'
            }
        }, {
            plugin: 'AddToCart',
            eventName: 'openOffCanvasCart',
            elementSelector: '.custom-select.js-offcanvas-cart-change-quantity',
            isSmall: true,
            event: {
                plugin: 'OffCanvasCart',
                callback: '_onChangeProductQuantity'
            }
        }, {
            plugin: 'OffCanvasCart',
            eventName: 'offCanvasOpened',
            elementSelector: '.custom-select.js-offcanvas-cart-change-quantity',
            isSmall: true,
            event: {
                plugin: 'OffCanvasCart',
                callback: '_onChangeProductQuantity'
            }
        }],
        gbmedQuantitiySelectOptions: {
            'isSmall': false,
            'btnClass': 'btn',
            'iconPlus': 'icon--plus',
            'iconMinus': 'icon--minus',
            'actionPause': 0.75,
            'icons': window.GbmedQuantitiySelect
        }
    };

    init() {
        const me = this;

        me.options.subscribes.forEach(obj => {
            if (obj.plugin === null) {
                me.create(obj);
            } else {
                const plugin = me.getPlugin(obj.plugin);
                if (plugin) {
                    plugin.$emitter.subscribe(obj.eventName, function () {
                        me.create(obj);
                    });
                }
            }
        });
    }

    create(obj) {
        const me = this;
        const opt = {...this.options.gbmedQuantitiySelectOptions};
        opt.isSmall = obj.isSmall;

        window.jQuery(obj.elementSelector).not('.select2-hidden-accessible').gbmedQuantitiySelect(
            opt
        ).select2({
            language: window.jQuery('html').attr('lang').substr(0,2)
        }).on('change.select2', function (event) {
            const formAutoSubmit = this.form.__plugins !== undefined
            && obj.event !== undefined
            && obj.event.plugin !== undefined
                ? this.form.__plugins.get(obj.event.plugin)
                : null;

            if(formAutoSubmit){
                formAutoSubmit[obj.event.callback](event);
            }else if(obj.event !== undefined){
                const plugin = me.getPlugin(obj.event.plugin);
                plugin[obj.event.callback](event);
            }
        });
    }

    getPlugin(pluginName) {
        const plugin = window.PluginManager.getPluginInstances(pluginName);

        if (plugin[0]) {
            return plugin[0];
        }

        return null;
    }
}
