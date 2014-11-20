var BuyersGuideController = Class.create(TRSCategoryBase, {

    _LOADING_STEP_ID: 'loading',
    _FINISHED_STEP_ID: 'done',
    _ERROR_STEP_ID: 'error',

    _DEFAULT_BG_CONTAINER_SELECTOR: '.buyersGuide',
    _DEFAULT_STEP_CONTAINER_SELECTOR: '.buyersGuide-questionMask',
    _DEFAULT_STEP_SELECTOR: '.buyersGuide-questions',
    _DEFAULT_BG_CAR_INPUT_SELECTOR: '.buyersGuide-carSelect',
    _DEFAULT_BG_SUPPLEMENT_INPUT_SELECTOR: '.buyersGuide-supplement',
    _DEFAULT_GO_BUTTON_ID: 'buyersGuideStartButton',
    _STEP_ID_ATTR_NAME: 'data-stepId',

    initialize: function($super, args) {
        var _args = args || {};
        this._moduleName = 'buyers_guide';
        this._isRunning = false;
        this.stepSelections = {};
        this.buyersGuideSelector = _args.buyersGuideSelector || this._DEFAULT_BG_CONTAINER_SELECTOR;
        this.carInputSelector = _args.carInputSelector || this._DEFAULT_BG_CAR_INPUT_SELECTOR;
        this.supplementInputSelector = _args.supplementInputSelector || this._DEFAULT_BG_SUPPLEMENT_INPUT_SELECTOR;
        this.goButtonId = _args.goButtonId || this._DEFAULT_GO_BUTTON_ID;
        this.updateCarInputsUrl = _args.updateCarInputsUrl || '';
        this.stepContainerSelector = _args.stepContainerSelector || this._DEFAULT_STEP_CONTAINER_SELECTOR,
        this.stepSelector = _args.stepSelector || this._DEFAULT_STEP_SELECTOR;

        this.register();
        this._initializeObservers();
        //$super(args);
    },

    register: function() {
        Event.fire($$('body')[0], this.REGISTER_EVENT, this, true);
    },

    _initializeObservers: function() {
        var carSelector = this.carInputSelector,
            stepContainerSelector = this.stepContainerSelector,
            goId = this.goButtonId,
            newDataEvent = this.NEW_DATA_EVENT,
            context = this;
        $$(carSelector).each(function(elt) {
            elt.observe('change', context.updateCarInputs.bind(context));
        });
        $$(stepContainerSelector).each(function(elt) {
            Event.on(elt, 'click', '.tile-select', context.handleStepSelection.bind(context));
        });
        $(goId).observe('click', context.startBuyersGuide.bind(context));
        //$(document).observe(newDataEvent, context.updateStateUrl.bind(context));
        $(document).observe(newDataEvent, context.handleNewCatalogData.bind(context));
        this._registerObserver = document.observe(this.INITIALIZED_EVENT, function() {
            context.register();
            context._registerObserver.stopObserving();
        });
        //$(document).observe()
        //$super();
    },


    handleStepSelection: function(evt) {
        console.log("Im handling it!");
        console.log(evt);
        var selectedButton = evt.target,
            selectedValue = selectedButton.readAttribute('data-value'),
            selectedStep = selectedButton.up('.buyersGuide-questions').readAttribute(this._STEP_ID_ATTR_NAME);
        this.stepSelections[selectedStep] = selectedValue;
        Event.fire(evt.target, this.FILTER_CHANGE_EVENT, evt.memo);
    },


    getFilters: function($super) {
        if (!this.isRunning()) {
            return {};
        }

        var filters = {
                car: this._getCarId(),
                buyersGuideActive: true
            },
            supplementSelector = this.supplementInputSelector,
            supplementData = {},
            stepSelections = this.stepSelections;

        $$(supplementSelector).each(function(elt) {
            supplementData[elt.readAttribute('name')] = elt.value;
        });
        filters = Object.extend(filters, supplementData);
        filters = Object.extend(filters, stepSelections);

        return filters;
    },


    isRunning: function() {
        return this._isRunning;
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
        var carId = this._getCarId(),
            containerSelector = this.buyersGuideSelector;
        if (carId) {
            this._isRunning = true;
            $$(containerSelector).each(function(elt) {
                elt.addClassName('active');
            });
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


    moveToStep: function(stepId) {
        console.log('moving to step [' + stepId + ']');
        var slideStrip = $$('.buyersGuide-questionWrap')[0],
            stepContainerSelector = this.stepContainerSelector,
            containerElt = $$(stepContainerSelector)[0],
            containerWidth = containerElt.getWidth(),
            multiplier = parseInt(stepId),
            previousStep = this._previousStep;

        if (this._ERROR_STEP_ID === previousStep) {
            this._showErrorStepElt(false);
        }

        if (isNaN(multiplier)) {
            if (this._LOADING_STEP_ID === stepId) {
                multiplier = 0;
            }
            if (this._ERROR_STEP_ID === stepId) {
                this._showErrorStepElt(true);
                multiplier = 0;
            }
            if (this._FINISHED_STEP_ID === stepId) {
                multiplier = this._getStepCount() - 1;
            }
        }
        this._previousStep = stepId;
        slideStrip.setStyle({'marginLeft': (-1 * multiplier * containerWidth).toString() + 'px'});
    },


    recommendProducts: function(recommendedSkus) {
        console.log('we recommend:');
        console.log(recommendedSkus);
        this.moveToStep(this._FINISHED_STEP_ID);
    },


    _getStepEltById: function(stepId) {
        var selectorTemplate = new Template('.buyersGuide-questions[#{attrName}="step_#{attrValue}"]'),
            selector = selectorTemplate.evaluate({attrName: this._STEP_ID_ATTR_NAME, attrValue: stepId}),
            stepElt = $$(selector)[0];
        return stepElt;
    },


    _showErrorStepElt: function(shouldShow) {
        var elt = this._getStepEltById(this._ERROR_STEP_ID);
        if (shouldShow) {
            elt.removeClassName('invisible');
        }
        else {
            elt.addClassName('invisible');
        }
    },


    _getStepCount: function() {
        return $$(this.stepSelector + ":not(.invisible)").length;
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
    },


    handleNewCatalogData: function(evt) {
        console.log("oh yay! new catalog data");
        console.log(evt);
        var newDom = $(evt.memo);
        var newGuideElt = newDom.select('#buyersGuideContainer')[0];
        var newActionElt = newGuideElt.select('#buyersGuideAction')[0];
        var newActionStr = newActionElt.value;
        var newActionObj = newActionStr.evalJSON();
        var newAction = newActionObj.action;
        console.log("new action:");
        console.log(newAction);
        this.takeAction(newAction);
    },

    takeAction: function(commandStr) {
        this._parseCommands(commandStr);
    },


    _parseCommands: function(actionStr) {
        var delimiter = ':',
            delimiterIndex = actionStr.indexOf(delimiter),
            command = '',
            remainder = '';
        if (delimiterIndex < 1) {
            console.log("ERROR: Could not find delimiter (#{delimiter}) in command string '#{commandStr}'.".interpolate({
                delimiter: delimiter,
                commandStr: actionStr
            }));
            return false;
        }
        command = actionStr.slice(0, delimiterIndex);
        remainder = actionStr.slice(delimiterIndex + 1);
        return this._parseCommand(command, remainder);
    },


    _parseCommand: function(command, remainder) {
        if (command === 'step') {
            return this._parseStep(remainder);
        }
        if (command === 'sku') {
            return this._parseSku(remainder);
        }
    },


    _parseStep: function(remainder) {
        var reTemplate = new Template("^(\\d+|#{loadId}|#{doneId}|#{errorId})(.*)$"),
            reStr = reTemplate.evaluate({
                loadId: this._LOADING_STEP_ID,
                doneId: this._FINISHED_STEP_ID,
                errorId: this._ERROR_STEP_ID
            }),
            re = new RegExp(reStr);
        var matches = re.exec(remainder),
            matchCount = matches.length,
            stepId = 'error',
            _remainder = remainder;

        if (matchCount >= 2) {
            stepId = matches[1];
        }

        this.moveToStep(stepId);

        _remainder = matchCount >= 3 ? matchCount[2] : '';

        return _remainder;
    },


    _parseSku: function(remainder) {
        var skus = remainder.split(',');
        console.log("here are my skus:");
        console.log(skus);
        this.recommendProducts(skus);
        return '';
    }


});