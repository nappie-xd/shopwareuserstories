import Plugin from 'src/plugin-system/plugin.class'
import DomAccess from 'src/helper/dom-access.helper'

export default class QuantitySelectPlugin extends Plugin {
    
    init() {
        try {
            this.minus = DomAccess.querySelector(this.el, '.decrease')
            this.plus = DomAccess.querySelector(this.el, '.increase')
            this.field = DomAccess.querySelector(this.el, 'input[type="number"]')
            this.trashcan = DomAccess.querySelector(this.el, '.remove')
        } catch (e) {
            return
        }
        try {
            if(this.field.value == parseInt(this.options.purchaseSteps)) {
                this.minus.setAttribute('style', 'display: none')
                this.trashcan.setAttribute('style', 'display: inline-block') 
            } 
            else { 
            this.minus.setAttribute('style', localStorage.getItem('minus-style'))
            this.trashcan.setAttribute('style', localStorage.getItem('remove-style'))
            } 
        } catch (e) {
            //do nothing
        }
        this.registerEvents()
    }

    registerEvents() {
        this.minus.addEventListener('click', this.decreaseQuantity.bind(this))
        this.plus.addEventListener('click', this.increaseQuantity.bind(this))
        this.field.addEventListener('change', this.refreshButtons.bind(this))      
    }

    /*
    * button switches to remove if quantity = 1
    */
    decreaseQuantity() {        
        const step = parseInt(this.options.purchaseSteps)
        const newQuant = parseInt(this.field.value) - step

        if(newQuant <= step) {
            try {
                localStorage.setItem('minus-style', 'display: none')
                localStorage.setItem('remove-style', 'display: inline-block')
            }
            catch(e) {
                //do nothing
            }
        }
        this.field.value = newQuant
        this.triggerChange() 
    }

    increaseQuantity() {
        const step = parseInt(this.options.purchaseSteps)
        const maxQuant = parseInt(this.options.maxQuantity)
        const newQuant = parseInt(this.field.value) + step

        if(newQuant >= maxQuant) {
            this.field.value = maxQuant
        }
        else if (this.field.value == step) {
            try {
                localStorage.setItem('minus-style', 'display: inline-block')
                localStorage.setItem('remove-style', 'display: none')
            } catch (e) {
                //do nothing
            }
            this.field.value = newQuant
        }
        else {
            this.field.value = newQuant
        }

        this.triggerChange()
    }

    triggerChange() {
        let changeEvent = new Event('change')
        this.field.dispatchEvent(changeEvent)
        
        let form = this.field.closest('form')
        form.dispatchEvent(changeEvent)
    }

    refreshButtons() {
        let step = parseInt(this.options.purchaseSteps)
        if(this.field.value >= step) {
            localStorage.setItem('minus-style', 'display: inline-block')
            localStorage.setItem('remove-style', 'display: none')
        }
    }
}