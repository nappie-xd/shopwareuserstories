(function ($, window) {
    function gbmedQuantitiySelect(el, opt) {
        this.options = opt;
        this.element = el;
        return this;
    }

    gbmedQuantitiySelect.prototype = {
        select: null,
        buttonPlus: null,
        buttonMinus: null,
        timeout: null,
        qsMin:null,
        qsMax:null,
        qsQuantity:null,
        actionPause: 0,
        init: function() {
            var me = this;
            me.select = $(me.element);
            me.setMinMax();
            me.getQuantity();
            me.isDisabled_class = ( me.select.is(':disabled') ) ? 'disabled' : 'enabled';
            me.actionPause = me.options.actionPause * 1000;
            me.quantityStep = me.getQuantityStep();

            me.select.wrap('<div></div>');
            me.form = me.select.closest('form');
            me.container = me.element.closest('div').addClass('quantitySelectContainer');
            if(me.options.isSmall){
                me.container.addClass('isSmall');
            }
            me.quantitySelectAllWrapper = $('<div/>', {'class': "quantitySelectAllWrapper"});
            me.quantitySelectAllWrapper.addClass( me.isDisabled_class );
            me.container.wrapInner(me.quantitySelectAllWrapper);
            me.container.closest('.select-field').addClass('noArrow');

            me.select.wrapAll('<div class="quantitySelectWrapper"></div>');
            me.quantitySelectWrapper = me.element.closest('div.quantitySelectWrapper');

            me.select.addClass('quantitySelect');
            me.buttonPlus = $('<button/>', {'class': me.options.btnClass+" quantitySelect rightButton"})
                .append($('<span/>', {'class': me.options.iconPlus}).html(me.options.icons.iconPlus));
            me.buttonMinus = $('<button/>', {'class': me.options.btnClass+" quantitySelect leftButton"})
                .append($('<span/>', {'class': me.options.iconMinus}).html(me.options.icons.iconMinus));

            me.quantitySelectWrapper.prepend(me.buttonMinus);
            me.quantitySelectWrapper.prepend(me.buttonPlus);

            me.selectedOption = me.select.find("option:selected");
            if( me.selectedOption.attr('disabled') == 'disabled' ){
                me.container.addClass('error');
            }

            // bind buttons only if selectbox is selectable
            if(me.isDisabled_class=='enabled'){

                me.buttonPlus.click(function(e){
                    e.preventDefault();
                    if(me.timeout) {
                        window.clearTimeout(me.timeout);
                        me.timeout = null;
                    }

                    if (me.qsQuantity < me.qsMax) {
                        me.qsQuantity += me.quantityStep;
                        me.select.data('quantity', me.qsQuantity);
                        me.select.val(me.qsQuantity);
                    }
                    me.setButtonsState();
                    me.timeout = setTimeout(function(){
                        me.select.change();
                        me.autoSubmit();
                    }, me.actionPause);
                });

                me.buttonMinus.click(function(e){
                    e.preventDefault();
                    if(me.timeout) {
                        window.clearTimeout(me.timeout);
                        me.timeout = null;
                    }

                    if (me.qsQuantity > me.qsMax) {
                        me.qsQuantity = me.qsMax;
                        me.select.val(me.qsQuantity);
                        me.select.data('quantity', me.qsQuantity);
                    } else if (me.qsQuantity > me.qsMin) {
                        me.qsQuantity -= me.quantityStep;
                        me.select.data('quantity', me.qsQuantity);
                        me.select.val(me.qsQuantity);
                    } else {
                        // do nothing
                    }
                    me.setButtonsState();
                    me.timeout = setTimeout(function(){
                        me.select.change();
                        me.autoSubmit();
                    }, me.actionPause);
                });
            }

            if(me.isDisabled_class=='disabled'){
                me.buttonMinus.prop('disabled', 'disabled');
                me.buttonMinus.off('click').click(function() { return false; });
                me.buttonPlus.prop('disabled', 'disabled');
                me.buttonPlus.off('click').click(function() { return false; });
            }
            //noinspection JSUnusedLocalSymbols
            me.select.change(function(e) {
                me.select.data('quantity', me.select.find("option:selected").val());
                me.setButtonsState();
                me.autoSubmit();
            });
            me.setButtonsState();
            me.select.show();
        },
        autoSubmit: function(){
            var me = this,
                isCheckout = $('body').hasClass('is-ctl-checkout');

            if(!isCheckout){
                return;
            }
            //me.form.submit();
        },
        setButtonsState: function() {
            var me = this;
            me.getQuantity();
            me.container.find('.select2-selection__rendered').html(me.qsQuantity).prop('title', me.qsQuantity);
            me.buttonPlus.prop('disabled', me.qsQuantity >= me.qsMax);
            me.buttonMinus.prop('disabled', me.qsQuantity <= me.qsMin);
        },
        setMinMax: function () {
            var me = this,
                min = $(me.select.find('option:first-child'))[0].value,
                max = $(me.select.find('option:last-child'))[0].value;
            me.qsMin = parseInt(min);
            me.qsMax = parseInt(max);
        },
        getQuantity: function () {
            var me = this,
                qsQuantity = $(me.select.find('option:selected'))[0].value;
            me.qsQuantity = parseInt(qsQuantity);
        },
        getQuantityStep: function () {
            var me = this,
                options = $(me.select.find('option')),
                step = 1;

            if(options.length > 1){
                step = parseInt(options[1].value) - parseInt(options[0].value);
            }
            return step;
        }
    };

    $.fn.gbmedQuantitiySelect = function (option) {
        opt = $.extend({
            isSmall: false,
            btnClass: 'btn btn-default is--primary',
            iconPlus: 'glyphicon glyphicon-plus',
            iconMinus: 'glyphicon glyphicon-minus',
            actionPause: 0,
            icons: null
        }, option);
        return this.each(function () {
            (new gbmedQuantitiySelect($(this), opt)).init()
        });
    };
})(jQuery, window);
