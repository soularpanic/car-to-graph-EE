var BuyersGuideController = Class.create(TRSCategoryBase, {

    _LOADING_STEP_ID: 'loading',
    _FINISHED_STEP_ID: 'done',
    _ERROR_STEP_ID: 'error',

    _DEFAULT_BG_CONTAINER_SELECTOR: '.buyersGuide',
    _DEFAULT_REEL_CONTAINER_SELECTOR: '.buyersGuide-questionMask',
    _DEFAULT_REEL_SELECTOR: '.buyersGuide-questionWrap',
    _DEFAULT_STEP_SELECTOR: '.buyersGuide-questions',
    _DEFAULT_STEP_OPTION_SELECTOR: '.tile',
    _DEFAULT_STEP_SELECT_BUTTON_SELECTOR: '.tile-select',
    _DEFAULT_BG_CAR_INPUT_SELECTOR: '.buyersGuide-carSelect',
    _DEFAULT_BG_SUPPLEMENT_INPUT_SELECTOR: '.buyersGuide-supplement',
    _DEFAULT_GO_BUTTON_ID: 'buyersGuideStartButton',
    _STEP_ID_ATTR_NAME: 'data-stepId',
    _OPTION_VALUE_ATTR_NAME: 'data-value',

    _DEFAULT_SELECTIONS_CONTENT: "<h2>We've got a few more questions before we can find the right parts for you...</h2>",

    initialize: function($super, args) {
        var _args = args || {};
        this._moduleName = 'buyers_guide';
        this._isRunning = false;
        this._previousStep = false;
        this.stepSelections = {};
        this.buyersGuideSelector = _args.buyersGuideSelector || this._DEFAULT_BG_CONTAINER_SELECTOR;
        this.carInputSelector = _args.carInputSelector || this._DEFAULT_BG_CAR_INPUT_SELECTOR;
        this.supplementInputSelector = _args.supplementInputSelector || this._DEFAULT_BG_SUPPLEMENT_INPUT_SELECTOR;
        this.goButtonId = _args.goButtonId || this._DEFAULT_GO_BUTTON_ID;
        this.updateCarInputsUrl = _args.updateCarInputsUrl || '';
        this.reelSelector = _args.reelSelector || this._DEFAULT_REEL_SELECTOR;
        this.reelContainerSelector = _args.reelContainerSelector || this._DEFAULT_REEL_CONTAINER_SELECTOR;
        this.stepSelector = _args.stepSelector || this._DEFAULT_STEP_SELECTOR;
        this.stepOptionSelector = _args.stepOptionSelector || this._DEFAULT_STEP_OPTION_SELECTOR;
        this.stepSelectButtonSelector = _args.stepSelectButtonSelector || this._DEFAULT_STEP_SELECT_BUTTON_SELECTOR;

        this.register();
        this._initializeObservers();
    },

    register: function() {
        Event.fire($$('body')[0], this.REGISTER_EVENT, this, true);
    },

    _initializeObservers: function() {
        var carSelector = this.carInputSelector,
            reelContainerSelector = this.reelContainerSelector,
            stepSelectButtonSelector = this.stepSelectButtonSelector,
            goId = this.goButtonId,
            newDataEvent = this.NEW_DATA_EVENT,
            context = this;
        $$(carSelector).each(function(elt) {
            elt.observe('change', context.updateCarInputs.bind(context));
        });
        $$(reelContainerSelector).each(function(elt) {
            Event.on(elt, 'click', stepSelectButtonSelector, context.handleStepSelection.bind(context));
        });
        $(goId).observe('click', context.startBuyersGuide.bind(context));
        $(document).observe(newDataEvent, context.handleNewCatalogData.bind(context));
        this._registerObserver = document.observe(this.INITIALIZED_EVENT, function() {
            context.register();
            context._registerObserver.stopObserving();
        });
    },


    handleStepSelection: function(evt) {
        var selectedButton = evt.target,
            selectedValue = selectedButton.readAttribute(this._OPTION_VALUE_ATTR_NAME),
            selectedStep = selectedButton.up(this.stepSelector).readAttribute(this._STEP_ID_ATTR_NAME);
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


    startBuyersGuide: function(evt) {
        var carId = this._getCarId(),
            containerSelector = this.buyersGuideSelector;
        if (carId) {
            this._isRunning = true;
            this.moveToStep(this._LOADING_STEP_ID);
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


    moveToStep: function(stepId, optionsToShowArr) {
        console.log('moving to step [' + stepId + ']');
        var slideStrip = $$('.buyersGuide-questionWrap')[0],
            reelContainerSelector = this.reelContainerSelector,
            reelContainer = $$(reelContainerSelector)[0],
            reelSelector = this.reelSelector,
            reel = $$(reelSelector)[0],
            containerWidth = reelContainer.getWidth(),
            multiplier = parseInt(stepId),
            step = false;

        this._cleanUpPreviousStep();
        this._prepareNextStep(stepId, optionsToShowArr);

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

        // adjust vertical height of guide
        q = this._getQByStepId(stepId);
        if (q) {
            reel.addClassName('toggle-' + q);
        }

        // move strip s.t. correct step is in frame
        slideStrip.setStyle({'marginLeft': (-1 * multiplier * containerWidth).toString() + 'px'});

        this._previousStep = stepId;
    },


    recommendProducts: function(recommendedSkus) {
        console.log('we recommend:');
        console.log(recommendedSkus);
        this.moveToStep(this._FINISHED_STEP_ID);
    },


    _prepareNextStep: function(stepId, optionsToShowArr) {
        var visibleOptions = optionsToShowArr,
            step = this._getStepEltById(stepId),
            stepOptionSelector = this.stepOptionSelector,
            optionButtonSelector = this.stepSelectButtonSelector,
            optionValueAttr = this._OPTION_VALUE_ATTR_NAME;
        // hide elements as necessary
        if (visibleOptions && visibleOptions.length) {
            visibleOptions.push('stock');
            var options = step.select(stepOptionSelector);
            options.each(function (option) {
                var optionButtons = option.select(optionButtonSelector);
                optionButtons.each(function (optionButton) {
                    var optionId = optionButton.readAttribute(optionValueAttr);
                    if ($A(optionsToShowArr).some(function (showableId) { return showableId === optionId; })) {
                        option.removeClassName('invisible');
                    }
                    else {
                        option.addClassName('invisible');
                    }
                });
            });
        }
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


    _cleanUpPreviousStep: function() {
        var previousStep = this._previousStep,
            previousElt = false,
            reelSelector = this.reelSelector,
            reel = $$(reelSelector)[0],
            q = false;

        if (false === previousStep) {
            return;
        }

        if (this._ERROR_STEP_ID === previousStep) {
            this._showErrorStepElt(false);
        }

        q = this._getQByStepId(previousStep);
        if (q) {
            reel.removeClassName("toggle-" + q);
        }
    },


    _getQByStepId: function(stepId) {
        var previousStep = this._previousStep,
            targetElt = false,
            reelSelector = this.reelSelector,
            reel = $$(reelSelector)[0],
            qArr = [],
            q = false;
        targetElt = this._getStepEltById(stepId);
        qArr = targetElt.classNames().grep(/^q\d$/);
        if (qArr.length > 0) {
            q = qArr[0];
            return q;
        }
        return false;
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
        var newDom = $(evt.memo);
        var newGuideElt = newDom.select('#buyersGuideContainer')[0];
        var newActionElt = newGuideElt.select('#buyersGuideAction')[0];
        var newActionStr = newActionElt.value;
        var newActionObj = newActionStr.evalJSON();
        var newAction = newActionObj.action;
        this.takeAction(newAction);
        this.updateSelectionControls();
    },


    updateSelectionControls: function() {
        var selections = this.stepSelections,
            defaultContent = this._DEFAULT_SELECTIONS_CONTENT,
            template = new Template("<h2>#{stepName}: <a class='buyersGuide-previousSelectionLink' data-stepId='#{stepId}'>#{stepValue}</a></h2>\n"),
            html = '';
        $H(selections).each(function(selection) {
            var stepId = selection.key.split('_')[1];
            html+= template.evaluate({
                stepName: selection.key,
                stepValue: selection.value,
                stepId: stepId
            });
        });
        if (html.length < 1) {
            html = defaultContent;
        }
        html = "<div class='buyersGuide-selections'>" + html + "</div>";
        $$('.buyersGuide-selections').each(function (selectionsContainer) {
           selectionsContainer.replace(html);
        });
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
        var reTemplate = new Template("^(\\d+|#{loadId}|#{doneId}|#{errorId})(?:\\[([^\\]]+)\\])?(.*)$"),
            reStr = reTemplate.evaluate({
                loadId: this._LOADING_STEP_ID,
                doneId: this._FINISHED_STEP_ID,
                errorId: this._ERROR_STEP_ID
            }),
            re = new RegExp(reStr);
        var matches = re.exec(remainder),
            matchCount = matches.length,
            stepId = 'error',
            optionsToShow = [],
            _remainder = remainder;

        if (matchCount >= 2) {
            stepId = matches[1];
        }

        if (matchCount >= 3 && matches[2]) {
            optionsToShow = matches[2].split(',').map(function (opt) { return opt.trim(); });
            console.log('options to show:');
            console.log(optionsToShow);
        }

        _remainder = matchCount >= 4 ? matchCount[3] : '';
        this.moveToStep(stepId, optionsToShow);

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