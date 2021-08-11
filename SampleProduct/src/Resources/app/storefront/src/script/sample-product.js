import Plugin from 'src/plugin-system/plugin.class'
import DomAccess from 'src/helper/dom-access.helper'

export default class SampleProductPlugin extends Plugin {
    init() {
        console.log("init started")
        try {
            console.log("label select try")
            this.label = DomAccess.querySelector(this.el, 'a[class="cart-item-label"]')
            console.log(this.label)
        } catch(e) {
            return
        }
        this.updateSampleLabel()
    }

    updateSampleLabel() {
        let sampleRef = this.label.getAttribute('href')
        let baseRef = sampleRef.slice(0, sampleRef.lastIndexOf('/')+1)
        baseRef = baseRef.slice(0, baseRef.lastIndexOf('-')) + '/'
        let productRef = sampleRef.slice(sampleRef.lastIndexOf('/')+1).replace("P-", "")
        this.label.setAttribute('href', baseRef + productRef) 
        console.log(baseRef)
        // this.label.setAttribute('href', productRef)
    }
}