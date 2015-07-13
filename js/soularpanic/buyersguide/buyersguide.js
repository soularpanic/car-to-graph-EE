var BuyersGuideController = Class.create(TRSCategoryBase, {

    _LOADING_STEP_ID: 'loading',
    _ROUGH_FITS_STEP_ID: 'done',
    _DIRECT_FITS_STEP_ID: 'directfit',
    _NO_FITS_STEP_ID: 'nofit',
    _ERROR_STEP_ID: 'error',
    _CONTACT_US_STEP_ID: 'contactus',

    _NEXT_KEYWORD: 'next',

    _DEFAULT_BG_CONTAINER_SELECTOR: '.buyersGuide',
//    _DEFAULT_REEL_CONTAINER_SELECTOR: '.buyersGuide-questionMask',
//    _DEFAULT_REEL_SELECTOR: '.buyersGuide-questionWrap',
//    _DEFAULT_STEP_SELECTOR: '.buyersGuide-questions',
//    _DEFAULT_STEP_OPTION_SELECTOR: '.tile',
//    _DEFAULT_STEP_SELECT_BUTTON_SELECTOR: '.tile-select',
//    _DEFAULT_STEP_HISTORY_BUTTON_SELECTOR: '.buyersGuide-previousSelectionLink',
    _DEFAULT_BG_CAR_INPUT_SELECTOR: '.buyersGuide-carSelect',
    _DEFAULT_BG_SUPPLEMENT_INPUT_SELECTOR: '.buyersGuide-supplement',
    _DEFAULT_GO_BUTTON_ID: 'buyersGuideStartButton',
    _DEFAULT_STOP_BUTTON_ID: 'buyersGuideStopButton',
    _DEFAULT_RESET_BUTTON_ID: 'buyersGuideResetButton',
//    _STEP_ID_ATTR_NAME: 'data-stepId',
//    _STEP_DISPLAY_NAME_ATTR_NAME: 'data-stepDisplayName',
//    _STEP_DISPLAY_VALUE_ATTR_NAME: 'data-displayValue',
//    _OPTION_ID_ATTR_NAME: 'data-id',
//    _OPTION_GROUP_ATTR_NAME: 'data-groupId',
//    _OPTION_VALUE_ATTR_NAME: 'data-value',

//    _DEFAULT_SELECTIONS_CONTENT: "<h2>We've got a few more questions before we can find the right parts for you...</h2>",
//    _DEFAULT_SELECTIONS_FIT_CONTENT: "<h2>Well, that was easy...</h2>",
//    _DEFAULT_SELECTIONS_NOFIT_CONTENT: "<h2>Hmm, that's interesting...</h2>",

//    _SPINNER_CLASS: 'buyersGuide-spinner',
//    _SPINNER_HTML: '<div class="buyersGuide-spinner">&nbsp;</div>',

    initialize: function($super, args) {
        var _args = args || {};
        this._moduleName = 'buyers_guide';
        this._isRunning = false;
//        this._previousStep = false;
//        this.stepSelections = [];
        this.buyersGuideSelector = _args.buyersGuideSelector || this._DEFAULT_BG_CONTAINER_SELECTOR;
        this.carInputSelector = _args.carInputSelector || this._DEFAULT_BG_CAR_INPUT_SELECTOR;
        this.carSelectController = _args.carSelectController;
        this.stepDisplayController = _args.stepDisplayController;
        this.supplementInputSelector = _args.supplementInputSelector || this._DEFAULT_BG_SUPPLEMENT_INPUT_SELECTOR;
        this.goButtonId = _args.goButtonId || this._DEFAULT_GO_BUTTON_ID;
        this.stopButtonId = _args.stopButtonId || this._DEFAULT_STOP_BUTTON_ID;
        this.resetButtonId = _args.resetButtonId || this._DEFAULT_RESET_BUTTON_ID;
        this.updateCarInputsUrl = _args.updateCarInputsUrl || '';
//        this.reelSelector = _args.reelSelector || this._DEFAULT_REEL_SELECTOR;
//        this.reelContainerSelector = _args.reelContainerSelector || this._DEFAULT_REEL_CONTAINER_SELECTOR;
//        this.stepSelector = _args.stepSelector || this._DEFAULT_STEP_SELECTOR;
//        this.stepOptionSelector = _args.stepOptionSelector || this._DEFAULT_STEP_OPTION_SELECTOR;
//        this.stepSelectButtonSelector = _args.stepSelectButtonSelector || this._DEFAULT_STEP_SELECT_BUTTON_SELECTOR;
//        this.historyStepSelectButtonSelector = _args.historyStepSelectButtonSelector || this._DEFAULT_STEP_HISTORY_BUTTON_SELECTOR;
//        this.noSelectionsText = _args.noSelectionsText || this._DEFAULT_SELECTIONS_CONTENT;
//        this.noSelectionsDirectFitText = _args.noSelectionsDirectFitText || this._DEFAULT_SELECTIONS_FIT_CONTENT;
//        this.noSelectionsNoFitText = _args.noSelectionsNoFitText || this._DEFAULT_SELECTIONS_NOFIT_CONTENT;

        this.register();
        this._initializeObservers();
    },

    register: function() {
        Event.fire($$('body')[0], this.REGISTER_EVENT, this, true);
    },

    _initializeObservers: function() {
//        var carSelector = this.carInputSelector,
        var reelContainerSelector = this.reelContainerSelector,
            stepSelectButtonSelector = this.stepSelectButtonSelector,
            historyStepSelectButtonSelector = this.historyStepSelectButtonSelector,
            goId = this.goButtonId,
            stopId = this.stopButtonId,
            resetId = this.resetButtonId,
            resetElt = $(resetId),
            newDataEvent = this.NEW_DATA_EVENT,
            context = this;
//        $$(reelContainerSelector).each(function(elt) {
//            Event.on(elt, 'click', stepSelectButtonSelector, context.handleStepSelection.bind(context));
//            Event.on(elt, 'click', historyStepSelectButtonSelector, context.handleHistorySelection.bind(context));
//        });
        $(goId).observe('click', context.startBuyersGuide.bind(context));
        if (resetElt) {
            resetElt.observe('click', function(evt) {
                context.resetBuyersGuide(evt);
                context.stopBuyersGuide(evt);
                Event.fire(evt.target, context.FILTER_CHANGE_EVENT, evt.memo);
            }.bind(context));
        }
        $(document).observe(newDataEvent, context.handleNewCatalogData.bind(context));
        this._registerObserver = document.observe(this.INITIALIZED_EVENT, function() {
            context.register();
            context._registerObserver.stopObserving();
        });
    },


//    handleStepSelection: function(evt) {
//        console.log("handling step selection");
//        var selectedButton = evt.target,
//            buttonSelector = this.stepSelectButtonSelector,
//            stepContainer = selectedButton.up(this.stepSelector),
//            selectedValue = selectedButton.readAttribute(this._OPTION_VALUE_ATTR_NAME),
//            displayValue = selectedButton.readAttribute(this._STEP_DISPLAY_VALUE_ATTR_NAME),
//            selectedStep = stepContainer.readAttribute(this._STEP_ID_ATTR_NAME),
//            stepDisplayName = stepContainer.readAttribute(this._STEP_DISPLAY_NAME_ATTR_NAME),
//            spinner = this._SPINNER_HTML;
//
//        this.stepSelections.push({
//            stepId: selectedStep,
//            value: selectedValue,
//            displayName: stepDisplayName,
//            displayValue: displayValue
//        });
//
//        selectedButton.insert({before: spinner});
//        stepContainer.select(buttonSelector).each(function(elt) { elt.addClassName('invisible'); });
//
//        Event.fire(evt.target, this.FILTER_CHANGE_EVENT, evt.memo);
//    },


//    handleHistorySelection: function(evt) {
//        var targetElt = evt.target,
//            stepId = targetElt.readAttribute('data-stepId'),
//            history = this.stepSelections,
//            acquired = false;
//        while (!acquired) {
//            var step = history.pop();
//            if (step.stepId === stepId) {
//                acquired = true;
//            }
//        }
//        Event.fire(evt.target, this.FILTER_CHANGE_EVENT, evt.memo);
//    },


    getFilters: function($super) {
        if (!this.isRunning()) {
            return {};
        }
        var filters = {
                car: this.carSelectController.getSelectedCarId(),
                buyersGuideActive: true
            },
            supplementSelector = this.supplementInputSelector,
            additionalData = {},
            stepSelections = this.stepDisplayController.getStepSelections();
//            stepSelections = this.stepSelections;

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


    startBuyersGuide: function(evt) {
        var carSelectController = this.carSelectController,
            carId = carSelectController.getSelectedCarId(),
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
    },


    resetBuyersGuide: function(evt) {
        this.carSelectController.reset();
        this.stepDisplayController.reset();
//        if (this._isRunning) {
//            this.stepSelections = [];
//        }
    },


    moveToStep: function(stepId, optionsToShowObj) {
        this.stepDisplayController.moveToStep(stepId, optionsToShowObj);
//        console.log('moving to step [' + stepId + ']');
//        var reelSelector = this.reelSelector,
//            reel = $$(reelSelector)[0],
//            _stepId = this._resolveStepId(stepId),
//            step = this._getStepEltById(_stepId),
//            q = false;
//
//        this._cleanUpPreviousStep();
//        this._prepareNextStep(_stepId, optionsToShowObj);
//
//        var styleOverride = step.select('.buyersGuide-select')[0].readAttribute('data-questionStyleOverride');
//        if (styleOverride) {
//            step.addClassName(styleOverride);
//        }
//        // adjust vertical height of guide
//        q = this._getQByStepId(_stepId);
//        if (q) {
//            reel.addClassName('toggle-' + q);
//
//        }
//
//        this._previousStep = _stepId;
    },


    recommendProducts: function(recommendedSkus) {
        console.log('we recommend:');
        console.log(recommendedSkus);
        this.moveToStep(this._ROUGH_FITS_STEP_ID);
    },


//    _resolveStepId: function(stepId) {
//        var specialIds = [
//                this._LOADING_STEP_ID,
//                this._ROUGH_FITS_STEP_ID,
//                this._DIRECT_FITS_STEP_ID,
//                this._NO_FITS_STEP_ID,
//                this._ERROR_STEP_ID,
//                this._CONTACT_US_STEP_ID
//            ],
//            stepSelections = this.stepSelections,
//            lastSelection = stepSelections.length > 0 ? stepSelections[stepSelections.length - 1] : null;
//        if (isNaN(parseInt(stepId))) {
//            if (specialIds.indexOf(stepId) > -1) {
//                return stepId;
//            }
//            if (stepId === this._NEXT_KEYWORD) {
//                if (lastSelection === null) {
//                    return 1;
//                }
//                else {
//                    var lastStepId = lastSelection.stepId,
//                        indexRe = /^step_(\d)$/,
//                        results = indexRe.exec(lastStepId),
//                        lastStepIndex = results.length > 0 ? results[1] : 0,
//                        lastStepIndexInt = parseInt(lastStepIndex);
//                    return lastStepIndexInt + 1;
//                }
//            }
//        }
//        return stepId;
//    },


    _getStepEltIndex: function(stepId) {
        var steps = this._getSteps(),
            target = this._getStepEltById(stepId),
            offset = $A(steps).lastIndexOf(target);
        return offset;
    },


//    _prepareNextStep: function(stepId, optionsToShowObj) {
//        var maskObj = optionsToShowObj,
//            step = this._getStepEltById(stepId);
//        // hide elements as necessary
//        $H(maskObj).each(function(mask) {
//            this._maskOptions(step, this['_OPTION_' + mask.key.toUpperCase() + '_ATTR_NAME'], mask.value);
//        }.bind(this));
//    },


//    _maskOptions: function(stepElt, attrName, attrValues) {
//        var stepOptionSelector = this.stepOptionSelector,
//            optionButtonSelector = this.stepSelectButtonSelector;
//
//        if (attrValues && attrValues.length) {
//            attrValues.push('stock');
//            var options = stepElt.select(stepOptionSelector);
//            options.each(function (option) {
//                var optionButtons = option.select(optionButtonSelector);
//                optionButtons.each(function (optionButton) {
//                    var optionId = optionButton.readAttribute(attrName);
//                    if ($A(attrValues).some(function (showableId) { return showableId === optionId; })) {
//                        option.removeClassName('invisible');
//                    }
//                    else {
//                        option.addClassName('invisible');
//                    }
//                });
//            });
//        }
//    },


//    _getStepEltById: function(stepId) {
//        var selectorTemplate = new Template('.buyersGuide-questions[#{attrName}="step_#{attrValue}"]'),
//            selector = selectorTemplate.evaluate({attrName: this._STEP_ID_ATTR_NAME, attrValue: stepId}),
//            stepElt = $$(selector)[0];
//        return stepElt;
//    },


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


//    _cleanUpPreviousStep: function() {
//        var previousStep = this._previousStep,
//            reelSelector = this.reelSelector,
//            stepSelectButtonSelector = this.stepSelectButtonSelector,
//            hiddenStepSelectButtonSelector = stepSelectButtonSelector + '.invisible',
//            spinnerSelector = '.' + this._SPINNER_CLASS,
//            reel = $$(reelSelector)[0],
//            q = false;
//
//        if (false === previousStep) {
//            return;
//        }
//        reel.select(spinnerSelector).each(function(elt) { elt.remove(); });
//
//
//
//        reel.select(hiddenStepSelectButtonSelector).each(function(elt) {
//            elt.removeClassName('invisible');
//        });
//
//        if (this._ERROR_STEP_ID === previousStep) {
//            this._showErrorStepElt(false);
//        }
//
//        var previousElt = this._getStepEltById(previousStep);
//        if (previousElt) {
//            var styleOverride = previousElt.select('.buyersGuide-select')[0].readAttribute('data-questionStyleOverride');
//            if (styleOverride) {
//                previousElt.removeClassName(styleOverride);
//            }
//        }
//
//        q = this._getQByStepId(previousStep);
//        if (q) {
//            reel.removeClassName("toggle-" + q);
//        }
//    },


//    _getQByStepId: function(stepId) {
//        var previousStep = this._previousStep,
//            targetElt = false,
//            reelSelector = this.reelSelector,
//            reel = $$(reelSelector)[0],
//            qArr = [],
//            q = false;
//        targetElt = this._getStepEltById(stepId);
//        qArr = targetElt.classNames().grep(/^q(\d|loading|directfit|contactus|done|nofit|error)$/);
//        if (qArr.length > 0) {
//            q = qArr[0];
//            return q;
//        }
//        return false;
//    },


    handleNewCatalogData: function(evt) {
        var newDom = $(evt.memo);
        var newGuideElt = newDom.select('#buyersGuideContainer')[0];
        var newActionElt = newGuideElt.select('#buyersGuideAction')[0];
        var newActionStr = newActionElt.value;
        var newActionObj = newActionStr.evalJSON();
        var newAction = newActionObj.action;
        this.takeAction(newAction);
//        this.updateSelectionControls();
    },


//    updateSelectionControls: function() {
//        var selections = this.stepSelections,
//            defaultContent = this.noSelectionsText,
//            defaultDoneContent = this.noSelectionsDirectFitText,
//            defaultNoFitContent = this.noSelectionsNoFitText,
//            template = new Template("<h2>#{stepName}: <a class='buyersGuide-previousSelectionLink' data-stepId='#{stepId}'>#{stepValue}</a></h2>\n"),
//            html = '',
//            previousId = this._previousStep;
//        if (selections.size() > 0) {
//            var backId = selections[selections.length - 1]['stepId'];
//            var prefix = isNaN(parseInt(backId)) ? "" : "step_";
//            html+= "<h2><a class='buyersGuide-previousSelectionLink' data-stepId='" + prefix + backId + "'>Back</a></h2>";
//        }
//
//        $A(selections).each(function(selection) {
//            var stepId = selection['stepId'];
//            html+= template.evaluate({
//                stepName: selection.displayName,
//                stepValue: selection.displayValue,
//                stepId: stepId
//            });
//        });
//        if (html.length < 1) {
//            if (previousId === this._ROUGH_FITS_STEP_ID || previousId === this._DIRECT_FITS_STEP_ID) {
//                html = defaultDoneContent;
//            }
//            else if (previousId === this._NO_FITS_STEP_ID || previousId === this._CONTACT_US_STEP_ID) {
//                html = defaultNoFitContent;
//            }
//            else {
//                html = defaultContent;
//            }
//        }
//        html = "<div class='buyersGuide-selections'>" + html + "</div>";
//        $$('.buyersGuide-selections').each(function (selectionsContainer) {
//            selectionsContainer.replace(html);
//        });
//    },


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
        var reTemplate = new Template("^(\\d+|#{nextKeyword}|#{loadId}|#{doneRoughId}|#{doneDirectId}|#{doneNadaId}|#{doneContactUsId}|#{errorId})(/\\w+)?(\\[[^\\]]+\\])?(.*)$"),
            reStr = reTemplate.evaluate({
                nextKeyword: this._NEXT_KEYWORD,
                loadId: this._LOADING_STEP_ID,
                doneRoughId: this._ROUGH_FITS_STEP_ID,
                doneDirectId: this._DIRECT_FITS_STEP_ID,
                doneNadaId: this._NO_FITS_STEP_ID,
                doneContactUsId: this._CONTACT_US_STEP_ID,
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