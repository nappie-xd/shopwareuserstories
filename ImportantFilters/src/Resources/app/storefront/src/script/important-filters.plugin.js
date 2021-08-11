import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

export default class ImportantFiltersPlugin extends Plugin {

    init() {
        try {
            console.log("filter init started");
            this.importantFilterButton = DomAccess.querySelector(this.el, '.emz-show-all-filters-button');
            this.secondFilter = DomAccess.querySelector(this.el, '.emz-second-filter');
            this.filterPanelContainer = DomAccess.querySelector(this.el, '.filter-panel-active-container');
            this.offcanvasFilterButton = DomAccess.querySelector(this.el, '.filter-panel-wrapper-toggle');         
        } catch(e) {
            return;
        }
        if (this.filterPanelContainer.innerHTML.trim().length != 0) {
            this.showFilters();
        }
        this.registerEvents();
    }

    registerEvents() {
        this.importantFilterButton.addEventListener('click', this.showFilters.bind(this));
        this.offcanvasFilterButton.addEventListener('click', this.reinit.bind(this));
        console.log("event registered");
    }

    showFilters() {
        this.secondFilter.setAttribute('style', 'display: inherit');
        this.importantFilterButton.setAttribute('style', 'display: none');
    }

    reinit() {
        try {
            console.log("filter reinit started");
            this.importantFilterButton = DomAccess.querySelector(this.el, '.emz-show-all-filters-button');
            this.secondFilter = DomAccess.querySelector(this.el, '.emz-second-filter');
            this.filterPanelContainer = DomAccess.querySelector(this.el, '.filter-panel-active-container');
        } catch(e) {
            return;
        }
        this.importantFilterButton.addEventListener('click', this.showFilters.bind(this));
    }
}