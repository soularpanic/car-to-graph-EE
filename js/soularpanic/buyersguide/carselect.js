var CarSelectController = Class.create({
    _DEFAULT_BG_CAR_INPUT_SELECTOR: '.buyersGuide-carSelect',

    initialize: function(args) {
        var _args = args || {};
        this.carInputSelector = _args.carInputSelector || this._DEFAULT_BG_CAR_INPUT_SELECTOR;
        this.updateCarInputsUrl = _args.updateCarInputsUrl || '';
        this._initializeObservers();
    },

    _initializeObservers: function() {
        var carSelector = this.carInputSelector,
            context = this;
        $$(carSelector).each(function(elt) {
            elt.observe('change', context.updateCarInputs.bind(context));
        });
    },

    updateCarInputs: function() {
        var selector = this.carInputSelector,
            url = this.updateCarInputsUrl,
            params = {},
            createHandler = this._handleUpdateInputAjaxCreate.bind(this),
            completeHandler = this._handleUpdateInputAjaxComplete.bind(this),
            successHandler = this._handleUpdateInputJson.bind(this);
        $$(selector).each(function(elt) {
            params[elt.name] = elt.value;
        });
        new Ajax.Request(url, {
            parameters: params,
            onCreate: function() {
                createHandler();
            },
            onSuccess: function(resp) {
                successHandler(resp.responseJSON);
            },
            onComplete: function(resp) {
                console.log(resp);
                completeHandler();
            }
        });
    },

    reset: function() {
        $$(this.carInputSelector).each(function (elt) {
            elt.setValue('');
        });
        this.updateCarInputs();

    },

    getSelectedCarId: function() {
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

    toggleCarInputs: function(state) {
        var inputs = $$('.buyersGuide-carSelect');
        state ? inputs.each(Form.Element.enable) : inputs.each(Form.Element.disable);
    },


    _handleUpdateInputAjaxCreate: function() {
        this.toggleCarInputs(false);
    },


    _handleUpdateInputAjaxComplete: function() {
        this.toggleCarInputs(true);
    },

    _handleUpdateInputJson: function(updateJson) {
        var selectorTemplate = new Template('[name="car[#{field}]"]'),
            optionTemplate = new Template('<option value="#{value}">#{value}</option>'),
            optgroupIdTemplate = new Template('buyersGuide-#{field}Recommend');

        $H(updateJson).each(function(pair) {
            var optgroupId = optgroupIdTemplate.evaluate({field: pair.key}),
                optgroup = $(optgroupId),
                fieldSelectSelector = selectorTemplate.evaluate({field: pair.key}),
                selectElt = $$(fieldSelectSelector)[0],
                currentSelectedOption = $$(fieldSelectSelector + " :selected"),
                currentVal = currentSelectedOption ? currentSelectedOption[0].value : '';
            console.log("currentVal:" + currentVal);

            var recommendedHtml = '';

            recommendedHtml += '<option value="">' + pair.key.capitalize() + '</option>';
            pair.value.each(function(val) {
                recommendedHtml += optionTemplate.evaluate({value: val});
            });


            selectElt.update(recommendedHtml);

            var optionToSelect = $$(fieldSelectSelector + ' option[value="' + currentVal + '"]:first');
            optionToSelect[0].writeAttribute('selected', 'selected');
        });
    }
});