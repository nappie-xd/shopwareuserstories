import Plugin from 'src/plugin-system/plugin.class'
import DomAccess from 'src/helper/dom-access.helper'

export default class ProductHoverPlugin extends Plugin {

    init() {
        console.log("js plugin started")
        try {
            let products = DomAccess.querySelectorAll(document, '.product-image')

            products.forEach(product => {
                product.onmouseover = () => {
                    if(!product.hasAttribute('data-image-preview'))
                        return
                    product.srcset = product.dataset.imagePreview
                    console.log("loaded")
                }
            })

            products.forEach(product => {
                product.onmouseleave = () => {
                    product.srcset = product.dataset.imageCover
                }
            })
            console.log("tried")
        } catch(e) {
            //do nothing
            console.log("did nothing")
        }
    }
}