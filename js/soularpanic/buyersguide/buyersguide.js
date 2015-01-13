var BuyersGuideController = Class.create(TRSCategoryBase, {

    _LOADING_STEP_ID: 'loading',
    _ROUGH_FITS_STEP_ID: 'done',
    _DIRECT_FITS_STEP_ID: 'directfit',
    _NO_FITS_STEP_ID: 'nofit',
    _ERROR_STEP_ID: 'error',

    _DEFAULT_BG_CONTAINER_SELECTOR: '.buyersGuide',
    _DEFAULT_REEL_CONTAINER_SELECTOR: '.buyersGuide-questionMask',
    _DEFAULT_REEL_SELECTOR: '.buyersGuide-questionWrap',
    _DEFAULT_STEP_SELECTOR: '.buyersGuide-questions',
    _DEFAULT_STEP_OPTION_SELECTOR: '.tile',
    _DEFAULT_STEP_SELECT_BUTTON_SELECTOR: '.tile-select',
    _DEFAULT_STEP_HISTORY_BUTTON_SELECTOR: '.buyersGuide-previousSelectionLink',
    _DEFAULT_BG_CAR_INPUT_SELECTOR: '.buyersGuide-carSelect',
    _DEFAULT_BG_SUPPLEMENT_INPUT_SELECTOR: '.buyersGuide-supplement',
    _DEFAULT_GO_BUTTON_ID: 'buyersGuideStartButton',
    _DEFAULT_STOP_BUTTON_ID: 'buyersGuideStopButton',
    _DEFAULT_RESET_BUTTON_ID: 'buyersGuideResetButton',
    _STEP_ID_ATTR_NAME: 'data-stepId',
    _STEP_DISPLAY_NAME_ATTR_NAME: 'data-stepDisplayName',
    _STEP_DISPLAY_VALUE_ATTR_NAME: 'data-displayValue',
    _OPTION_ID_ATTR_NAME: 'data-id',
    _OPTION_GROUP_ATTR_NAME: 'data-groupId',
    _OPTION_VALUE_ATTR_NAME: 'data-value',

    _DEFAULT_SELECTIONS_CONTENT: "<h2>We've got a few more questions before we can find the right parts for you...</h2>",
    _DEFAULT_SELECTIONS_FIT_CONTENT: "<h2>Well, that was easy...</h2>",
    _DEFAULT_SELECTIONS_NOFIT_CONTENT: "<h2>Hmm, that's interesting...</h2>",

    initialize: function($super, args) {
        var _args = args || {};
        this._moduleName = 'buyers_guide';
        this._isRunning = false;
        this._previousStep = false;
        this.stepSelections = [];
        this.buyersGuideSelector = _args.buyersGuideSelector || this._DEFAULT_BG_CONTAINER_SELECTOR;
        this.carInputSelector = _args.carInputSelector || this._DEFAULT_BG_CAR_INPUT_SELECTOR;
        this.supplementInputSelector = _args.supplementInputSelector || this._DEFAULT_BG_SUPPLEMENT_INPUT_SELECTOR;
        this.goButtonId = _args.goButtonId || this._DEFAULT_GO_BUTTON_ID;
        this.stopButtonId = _args.stopButtonId || this._DEFAULT_STOP_BUTTON_ID;
        this.resetButtonId = _args.resetButtonId || this._DEFAULT_RESET_BUTTON_ID;
        this.updateCarInputsUrl = _args.updateCarInputsUrl || '';
        this.reelSelector = _args.reelSelector || this._DEFAULT_REEL_SELECTOR;
        this.reelContainerSelector = _args.reelContainerSelector || this._DEFAULT_REEL_CONTAINER_SELECTOR;
        this.stepSelector = _args.stepSelector || this._DEFAULT_STEP_SELECTOR;
        this.stepOptionSelector = _args.stepOptionSelector || this._DEFAULT_STEP_OPTION_SELECTOR;
        this.stepSelectButtonSelector = _args.stepSelectButtonSelector || this._DEFAULT_STEP_SELECT_BUTTON_SELECTOR;
        this.historyStepSelectButtonSelector = _args.historyStepSelectButtonSelector || this._DEFAULT_STEP_HISTORY_BUTTON_SELECTOR;
        this.noSelectionsText = _args.noSelectionsText || this._DEFAULT_SELECTIONS_CONTENT;
        this.noSelectionsDirectFitText = _args.noSelectionsDirectFitText || this._DEFAULT_SELECTIONS_FIT_CONTENT;
        this.noSelectionsNoFitText = _args.noSelectionsNoFitText || this._DEFAULT_SELECTIONS_NOFIT_CONTENT;

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
            historyStepSelectButtonSelector = this.historyStepSelectButtonSelector,
            goId = this.goButtonId,
            stopId = this.stopButtonId,
            resetId = this.resetButtonId,
            newDataEvent = this.NEW_DATA_EVENT,
            context = this;
        $$(carSelector).each(function(elt) {
            elt.observe('change', context.updateCarInputs.bind(context));
        });
        $$(reelContainerSelector).each(function(elt) {
            Event.on(elt, 'click', stepSelectButtonSelector, context.handleStepSelection.bind(context));
            Event.on(elt, 'click', historyStepSelectButtonSelector, context.handleHistorySelection.bind(context));
        });
        $(goId).observe('click', context.startBuyersGuide.bind(context));
        $(stopId).observe('click', context.stopBuyersGuide.bind(context));
//        $(resetId).observe('click', context.resetBuyersGuide.bind(context)); // reset may not be a useful function; perhaps remove?
        $(document).observe(newDataEvent, context.handleNewCatalogData.bind(context));
        this._registerObserver = document.observe(this.INITIALIZED_EVENT, function() {
            context.register();
            context._registerObserver.stopObserving();
        });
    },


    handleStepSelection: function(evt) {
        console.log("handling step selection");
        var selectedButton = evt.target,
            stepContainer = selectedButton.up(this.stepSelector),
            selectedValue = selectedButton.readAttribute(this._OPTION_VALUE_ATTR_NAME),
            displayValue = selectedButton.readAttribute(this._STEP_DISPLAY_VALUE_ATTR_NAME),
            selectedStep = stepContainer.readAttribute(this._STEP_ID_ATTR_NAME),
            stepDisplayName = stepContainer.readAttribute(this._STEP_DISPLAY_NAME_ATTR_NAME);

        this.stepSelections.push({
            stepId: selectedStep,
            value: selectedValue,
            displayName: stepDisplayName,
            displayValue: displayValue
        });
        Event.fire(evt.target, this.FILTER_CHANGE_EVENT, evt.memo);
    },


    handleHistorySelection: function(evt) {
        var targetElt = evt.target,
            stepId = targetElt.readAttribute('data-stepId'),
            history = this.stepSelections,
            acquired = false;
        while (!acquired) {
            var step = history.pop();
            if (step.stepId === stepId) {
                acquired = true;
            }
        }
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
            additionalData = {},
            stepSelections = this.stepSelections;

        $$(supplementSelector).each(function(elt) {
            additionalData[elt.readAttribute('name')] = elt.value;
        });

        $A(stepSelections).each(function(selection) {
            additionalData[selection.stepId] = selection.value;
        });

        filters = Object.extend(filters, additionalData);


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
            Event.fire(evt.target, this.SET_ACTIVE_REGISTRANTS_EVENT, [this._moduleName], true);
            Event.fire(evt.target, this.FILTER_CHANGE_EVENT, evt.memo);
        }
    },


    stopBuyersGuide: function(evt) {
        if (!this._isRunning) {
            return;
        }
        this._isRunning = false;
        $$(this.buyersGuideSelector).each(function(elt) {
            elt.removeClassName('active');
        });
        Event.fire(evt.target, this.SET_ACTIVE_REGISTRANTS_EVENT, [], true);
        Event.fire(evt.target, this.FILTER_CHANGE_EVENT, evt.memo);
    },


    resetBuyersGuide: function(evt) {
        $$(this.carInputSelector).each(function (elt) {
            elt.setValue('');
        });
        this.updateCarInputs();

        if (this._isRunning) {
            this.stepSelections = [];
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


    moveToStep: function(stepId, optionsToShowObj) {
        console.log('moving to step [' + stepId + ']');
        var slideStrip = $$('.buyersGuide-questionWrap')[0],
            reelContainerSelector = this.reelContainerSelector,
            reelContainer = $$(reelContainerSelector)[0],
            reelSelector = this.reelSelector,
            reel = $$(reelSelector)[0],
            containerWidth = reelContainer.getWidth(),
            multiplier = parseInt(stepId),
            step = false,
            q = false;

        this._cleanUpPreviousStep();
        this._prepareNextStep(stepId, optionsToShowObj);

        if (isNaN(multiplier)) {
            var isStepId = function(step) { return step === stepId; }
            if (isStepId(this._LOADING_STEP_ID)) {
                multiplier = 0;
            }
            if (isStepId(this._ERROR_STEP_ID)) {
                this._showErrorStepElt(true);
                multiplier = 0;
            }
            if ($A([this._ROUGH_FITS_STEP_ID, this._DIRECT_FITS_STEP_ID, this._NO_FITS_STEP_ID]).some(isStepId)) {
                multiplier = this._getStepEltIndex(stepId);
            }
        }

        // adjust vertical height of guide
        q = this._getQByStepId(stepId);
        if (q) {
            reel.addClassName('toggle-' + q);
        }

        // move strip s.t. correct step is in frame
        // slideStrip.setStyle({'marginLeft': (-1 * multiplier * containerWidth).toString() + 'px'});

        this._previousStep = stepId;
    },


    recommendProducts: function(recommendedSkus) {
        console.log('we recommend:');
        console.log(recommendedSkus);
        this.moveToStep(this._ROUGH_FITS_STEP_ID);
    },


    _getStepEltIndex: function(stepId) {
        var steps = this._getSteps(),
            target = this._getStepEltById(stepId),
            offset = $A(steps).lastIndexOf(target);
        return offset;
    },


    _prepareNextStep: function(stepId, optionsToShowObj) {
        var maskObj = optionsToShowObj,
            step = this._getStepEltById(stepId);
        // hide elements as necessary
        $H(maskObj).each(function(mask) {
            this._maskOptions(step, this['_OPTION_' + mask.key.toUpperCase() + '_ATTR_NAME'], mask.value);
        }.bind(this));
    },


    _maskOptions: function(stepElt, attrName, attrValues) {
        var stepOptionSelector = this.stepOptionSelector,
            optionButtonSelector = this.stepSelectButtonSelector;

        if (attrValues && attrValues.length) {
            attrValues.push('stock');
            var options = stepElt.select(stepOptionSelector);
            options.each(function (option) {
                var optionButtons = option.select(optionButtonSelector);
                optionButtons.each(function (optionButton) {
                    var optionId = optionButton.readAttribute(attrName);
                    if ($A(attrValues).some(function (showableId) { return showableId === optionId; })) {
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
        var steps = this._getSteps();
        return steps.length;
    },


    _getSteps: function() {
        return $$(this.stepSelector + ":not(.invisible)");
    },


    _cleanUpPreviousStep: function() {
        var previousStep = this._previousStep,
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
        qArr = targetElt.classNames().grep(/^q(\d|loading|directfit|done|nofit|error)$/);
        if (qArr.length > 0) {
            q = qArr[0];
            return q;
        }
        return false;
    },

    _handleUpdateInputJson: function(updateJson) {
        var selectorTemplate = new Template('[name="car[#{field}]"]'),
            optionTemplate = new Template('<option value="#{value}">#{value}</option>'),
            optgroupIdTemplate = new Template('buyersGuide-#{field}Recommend'),
            optgroupEltTemplate = new Template('<optgroup label="Suggested #{field}s" id="#{id}"></optgroup>');
        $H(updateJson).each(function(pair) {
            var optgroupId = optgroupIdTemplate.evaluate({field: pair.key}),
                optgroup = $(optgroupId),
                fieldSelectSelector = selectorTemplate.evaluate({field: pair.key}),
                selectElt = $$(fieldSelectSelector)[0],
                currentSelectedOption = $$(fieldSelectSelector + " :selected"),
                currentVal = currentSelectedOption ? currentSelectedOption[0].value : '';
            console.log("currentVal:" + currentVal);

            var recommendedHtml = '';

//            if (!optgroup) {
//                var selectElt = $$(fieldSelectSelector)[0],
//                    firstOption = selectElt.childElements()[0],
//                    optgroupHtml = optgroupEltTemplate.evaluate({field: pair.key, id: optgroupId});
//                firstOption.insert({after: optgroupHtml});
//                optgroup = $(optgroupId);
//            }

            recommendedHtml += '<option value="">' + pair.key.capitalize() + '</option>';
            pair.value.each(function(val) {
                recommendedHtml += optionTemplate.evaluate({value: val});
            });


//            optgroup.update(recommendedHtml);
            selectElt.update(recommendedHtml);

            var optionToSelect = $$(fieldSelectSelector + ' option[value="' + currentVal + '"]:first');
//            var blankOption = $$(fieldSelectSelector + ' option[value=""]:first');
            optionToSelect[0].writeAttribute('selected', 'selected');
//            blankOption[0].update(currentVal === '' ? pair.key.capitalize() : 'Refresh Suggestions')
        });
    },


    handleNewCatalogData: function(evt) {
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
            defaultContent = this.noSelectionsText,
            defaultDoneContent = this.noSelectionsDirectFitText,
            defaultNoFitContent = this.noSelectionsNoFitText,
            template = new Template("<h2>#{stepName}: <a class='buyersGuide-previousSelectionLink' data-stepId='#{stepId}'>#{stepValue}</a></h2>\n"),
            html = '',
            previousId = this._previousStep;
        if (selections.size() > 0) {
            var backId = selections[selections.length - 1]['stepId'];
            var prefix = isNaN(parseInt(backId)) ? "" : "step_";
            html+= "<h2><a class='buyersGuide-previousSelectionLink' data-stepId='" + prefix + backId + "'>Back</a></h2>";
        }

        $A(selections).each(function(selection) {
            var stepId = selection['stepId'];
            html+= template.evaluate({
                stepName: selection.displayName,
                stepValue: selection.displayValue,
                stepId: stepId
            });
        });
        if (html.length < 1) {
            if (previousId === this._ROUGH_FITS_STEP_ID || previousId === this._DIRECT_FITS_STEP_ID) {
                html = defaultDoneContent;
            }
            else if (previousId === this._NO_FITS_STEP_ID) {
                html = defaultNoFitContent;
            }
            else {
                html = defaultContent;
            }
        }
        html = "<div class='buyersGuide-selections'>" + html + "</div>";
        $$('.buyersGuide-selections').each(function (selectionsContainer) {
            selectionsContainer.replace(html);
        });
    },


    takeAction: function(commandStr) {
        console.log("Taking action: " + commandStr);
        this._parseCommands(commandStr);
    },


    _parseCommands: function(actionStr) {
        console.log("Actions: " + actionStr);
        var commands = actionStr.split(';'),
            parser = this._parseCommand.bind(this);
        console.log("commands:");
        console.log(commands);
        commands.each(function(commandStr) {
            console.log("command: " + commandStr);
            var delimiter = ':',
                delimiterIndex = commandStr.indexOf(delimiter),
                command = '',
                remainder = '';

            command = commandStr.slice(0, delimiterIndex);
            remainder = commandStr.slice(delimiterIndex + 1);
            console.log("after split: " + command + '/' + remainder);
            parser(command.trim(), remainder.trim());
        });

        return true;
    },


    _parseCommand: function(command, remainder) {
        console.log("_parseCommand - start");
        if (command === 'step') {
            return this._parseStep(remainder);
        }
        else if (command === 'sku') {
            return this._parseSku(remainder);
        }
        else {
            console.log("Unhandled command: [" + command + "]/[" + remainder + "]");
        }
    },


    _parseStep: function(remainder) {
        console.log("Parsing step -" + remainder + "-");
        var reTemplate = new Template("^(\\d+|#{loadId}|#{doneRoughId}|#{doneDirectId}|#{doneNadaId}|#{errorId})(/\\w+)?(\\[[^\\]]+\\])?(.*)$"),
            reStr = reTemplate.evaluate({
                loadId: this._LOADING_STEP_ID,
                doneRoughId: this._ROUGH_FITS_STEP_ID,
                doneDirectId: this._DIRECT_FITS_STEP_ID,
                doneNadaId: this._NO_FITS_STEP_ID,
                errorId: this._ERROR_STEP_ID
            }),
            re = new RegExp(reStr);
        var matches = re.exec(remainder),
            matchCount = matches.length,
            stepId = 'error',
            optionsToShow = {},
            _remainder = remainder;

        if (matchCount >= 2) {
            stepId = matches[1];
        }

        if (matchCount >= 3 && matches[2]) {
            if (matches[2].charAt(0) === '/') {
                optionsToShow['group'] = matches[2].substr(1).split(',');
            }
        }

        if (matchCount >= 4 && matches[3]) {
            if (matches[3].charAt(0) === '[') {
                optionsToShow['id'] = matches[3].substr(1, matches[3].length - 2).split(',').map(function (opt) { return opt.trim(); });
            }
        }

        _remainder = matchCount >= 5 ? matchCount[4] : '';
        this.moveToStep(stepId, optionsToShow);

        return _remainder;
    },


    _parseSku: function(remainder) {
        var skus = remainder.split(',');
        console.log("here are my skus:");
        console.log(skus);
        this.recommendProducts(skus);
        return '';
    },


    _parseSql: function(remainder) {
        console.log("i don't do sql");
        return '';
    },


    _parseDone: function(remainder) {
        console.log("i'm done!");
        return '';
    }

});