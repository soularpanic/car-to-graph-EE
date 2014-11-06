var BuyersGuideController = Class.create(TRSCategoryBase, {

    _DEFAULT_BG_CAR_INPUT_SELECTOR: '.buyersGuide-carSelect',
    _DEFAULT_GO_BUTTON_ID: 'buyersGuideStartButton',

    initialize: function($super, args) {
        var _args = args || {};
        this._moduleName = 'buyers_guide';
        this.carInputSelector = _args.carInputSelector || this._DEFAULT_BG_CAR_INPUT_SELECTOR;
        this.goButtonId = _args.goButtonId || this._DEFAULT_GO_BUTTON_ID;
        this.updateCarInputsUrl = _args.updateCarInputsUrl || '';
        this.register();
        this._initializeObservers();
        //$super(args);
    },

    register: function() {
        Event.fire($$('body')[0], this.REGISTER_EVENT, this, true);
    },

    _initializeObservers: function() {
        var carSelector = this.carInputSelector,
            goId = this.goButtonId,
            newDataEvent = this.NEW_DATA_EVENT,
            context = this;
        $$(carSelector).each(function(elt) {
            elt.observe('change', context.updateCarInputs.bind(context));
        });
        $(goId).observe('click', context.startBuyersGuide.bind(context));
        //$(document).observe(newDataEvent, context.updateStateUrl.bind(context));
        this._registerObserver = document.observe(this.INITIALIZED_EVENT, function() {
            context.register();
            context._registerObserver.stopObserving();
        });
        //$(document).observe()
        //$super();
    },


    getFilters: function($super) {
        var carId = this._getCarId();
        return carId ? {car: carId} : {};
    },


    _getCarId: function() {
        var inputSelector = this.carInputSelector,
            nameRe = /car\[(\w+)\]/,
            facets = {},
            template = new Template("#{make}_#{model}_#{year}"),
            complete = true;
        $$(inputSelector).each(function(elt) {
            var matches = nameRe.exec(elt.name),
                key = matches[1];

            if (!elt.value) {
                complete = false;
            }
            facets[key] = elt.value;
        });
        return complete ? template.evaluate(facets).toLowerCase() : false;
    },


//    updateStateUrl: function(evt) {
//        var elt = evt.memo.select('#filterComponents')[0];
//        var filterComponents = $(elt).readAttribute('data-filterComponents');
//        $('buyersGuideStartUrl').setValue(filterComponents);
//    },


    startBuyersGuide: function(evt) {
        var carId = this._getCarId();
        if (carId) {
            Event.fire(evt.target, this.FILTER_CHANGE_EVENT, evt.memo);
        }
    },


    updateCarInputs: function() {
        var selector = this.carInputSelector,
            url = this.updateCarInputsUrl,
            params = {},
            successHandler = this._handleUpdateInputJson.bind(this);
        $$(selector).each(function(elt) {
            params[elt.name] = elt.value;
        });
        new Ajax.Request(url, {
            parameters: params,
            onSuccess: function(resp) {
                successHandler(resp.responseJSON);
            },
            onComplete: function(resp) {
                console.log(resp);
            }
        });
    },


    _handleUpdateInputJson: function(updateJson) {
        var selectorTemplate = new Template('[name="car[#{field}]"]'),
            optionTemplate = new Template('<option value="#{value}">#{value}</option>');
        $H(updateJson).each(function(pair) {
            var optionsHtml = '<option value="">' + pair.key.capitalize() + '</option>';

            if (pair.value.size() === 1) {
                var val = pair.value[0];
                optionsHtml += '<option selected="selected" value="' + val + '">' + val + '</option>';
            }
            else {
                pair.value.each(function(val) {
                    optionsHtml += optionTemplate.evaluate({value: val});
                });
            }

            $$(selectorTemplate.evaluate({field: pair.key})).each(function(elt) {
                elt.update(optionsHtml);
            });
        });
    }
});