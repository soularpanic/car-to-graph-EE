var StepDisplayControllerInterface = Class.create({

    _LOADING_STEP_ID: 'loading',
    _ROUGH_FITS_STEP_ID: 'done',
    _DIRECT_FITS_STEP_ID: 'directfit',
    _NO_FITS_STEP_ID: 'nofit',
    _ERROR_STEP_ID: 'error',
    _CONTACT_US_STEP_ID: 'contactus',

    FILTER_CHANGE_EVENT: 'trs:filter_change',

    _NEXT_KEYWORD: 'next',

    initialize: function(args) {},
    moveToStep: function(stepId, optionsToShowObj) {},
//    handleNewCatalogData: function(evt) {},
    getStepSelections: function() {},
    reset: function() {}
});

var QSlideStepDisplayController = Class.create(StepDisplayControllerInterface, {

    _DEFAULT_REEL_CONTAINER_SELECTOR: '.buyersGuide-questionMask',
    _DEFAULT_REEL_SELECTOR: '.buyersGuide-questionWrap',
    _DEFAULT_STEP_HISTORY_BUTTON_SELECTOR: '.buyersGuide-previousSelectionLink',
    _DEFAULT_STEP_OPTION_SELECTOR: '.tile',
    _DEFAULT_STEP_SELECT_BUTTON_SELECTOR: '.tile-select',
    _DEFAULT_STEP_SELECTOR: '.buyersGuide-questions',
    _OPTION_GROUP_ATTR_NAME: 'data-groupId',
    _OPTION_ID_ATTR_NAME: 'data-id',
    _OPTION_VALUE_ATTR_NAME: 'data-value',
    _STEP_DISPLAY_NAME_ATTR_NAME: 'data-stepDisplayName',
    _STEP_DISPLAY_VALUE_ATTR_NAME: 'data-displayValue',
    _STEP_ID_ATTR_NAME: 'data-stepId',

    _DEFAULT_SELECTIONS_CONTENT: "<h2>We've got a few more questions before we can find the right parts for you...</h2>",
    _DEFAULT_SELECTIONS_FIT_CONTENT: "<h2>Well, that was easy...</h2>",
    _DEFAULT_SELECTIONS_NOFIT_CONTENT: "<h2>Hmm, that's interesting...</h2>",

    _SPINNER_CLASS: 'buyersGuide-spinner',
    _SPINNER_HTML: '<div class="buyersGuide-spinner">&nbsp;</div>',

   initialize: function(args) {
       var _args = args || {};
       this._previousStep = false;
       this.stepSelections = [];
       this.stepSelector = _args.stepSelector || this._DEFAULT_STEP_SELECTOR;
       this.stepSelectButtonSelector = _args.stepSelectButtonSelector || this._DEFAULT_STEP_SELECT_BUTTON_SELECTOR;
       this.stepOptionSelector = _args.stepOptionSelector || this._DEFAULT_STEP_OPTION_SELECTOR;
       this.historyStepSelectButtonSelector = _args.historyStepSelectButtonSelector || this._DEFAULT_STEP_HISTORY_BUTTON_SELECTOR;
       this.reelSelector = _args.reelSelector || this._DEFAULT_REEL_SELECTOR;
       this.reelContainerSelector = _args.reelContainerSelector || this._DEFAULT_REEL_CONTAINER_SELECTOR;
       this.noSelectionsText = _args.noSelectionsText || this._DEFAULT_SELECTIONS_CONTENT;
       this.noSelectionsDirectFitText = _args.noSelectionsDirectFitText || this._DEFAULT_SELECTIONS_FIT_CONTENT;
       this.noSelectionsNoFitText = _args.noSelectionsNoFitText || this._DEFAULT_SELECTIONS_NOFIT_CONTENT;
       this._initializeObservers();
   },

    _initializeObservers: function() {
        var reelContainerSelector = this.reelContainerSelector,
            stepSelectButtonSelector = this.stepSelectButtonSelector,
            historyStepSelectButtonSelector = this.historyStepSelectButtonSelector,
            context = this;

        $$(reelContainerSelector).each(function(elt) {
            Event.on(elt, 'click', stepSelectButtonSelector, context.handleStepSelection.bind(context));
            Event.on(elt, 'click', historyStepSelectButtonSelector, context.handleHistorySelection.bind(context));
        });
//        $(document).observe(newDataEvent, context.handleNewCatalogData.bind(context));
    },

    moveToStep: function(stepId, optionsToShowObj) {
        console.log('moving to step [' + stepId + ']');
        var reelSelector = this.reelSelector,
            reel = $$(reelSelector)[0],
            _stepId = this._resolveStepId(stepId),
            step = this._getStepEltById(_stepId),
            q = false;

        this._cleanUpPreviousStep();
        this._prepareNextStep(_stepId, optionsToShowObj);

        var styleOverride = step.select('.buyersGuide-select')[0].readAttribute('data-questionStyleOverride');
        if (styleOverride) {
            step.addClassName(styleOverride);
        }
        // adjust vertical height of guide
        q = this._getQByStepId(_stepId);
        if (q) {
            reel.addClassName('toggle-' + q);

        }

        this._previousStep = _stepId;

        this.updateSelectionControls();
    },

    getStepSelections: function() {
        return this.stepSelections;
    },

    reset: function() {
        this.stepSelections = [];
    },

    handleNewCatalogData: function(evt) {

    },

    handleStepSelection: function(evt) {
        console.log("handling step selection");
        var selectedButton = evt.target,
            buttonSelector = this.stepSelectButtonSelector,
            stepContainer = selectedButton.up(this.stepSelector),
            selectedValue = selectedButton.readAttribute(this._OPTION_VALUE_ATTR_NAME),
            displayValue = selectedButton.readAttribute(this._STEP_DISPLAY_VALUE_ATTR_NAME),
            selectedStep = stepContainer.readAttribute(this._STEP_ID_ATTR_NAME),
            stepDisplayName = stepContainer.readAttribute(this._STEP_DISPLAY_NAME_ATTR_NAME),
            spinner = this._SPINNER_HTML;

        this.stepSelections.push({
            stepId: selectedStep,
            value: selectedValue,
            displayName: stepDisplayName,
            displayValue: displayValue
        });

        selectedButton.insert({before: spinner});
        stepContainer.select(buttonSelector).each(function(elt) { elt.addClassName('invisible'); });

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
            else if (previousId === this._NO_FITS_STEP_ID || previousId === this._CONTACT_US_STEP_ID) {
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

    _resolveStepId: function(stepId) {
        var specialIds = [
                this._LOADING_STEP_ID,
                this._ROUGH_FITS_STEP_ID,
                this._DIRECT_FITS_STEP_ID,
                this._NO_FITS_STEP_ID,
                this._ERROR_STEP_ID,
                this._CONTACT_US_STEP_ID
            ],
            stepSelections = this.stepSelections,
            lastSelection = stepSelections.length > 0 ? stepSelections[stepSelections.length - 1] : null;
        if (isNaN(parseInt(stepId))) {
            if (specialIds.indexOf(stepId) > -1) {
                return stepId;
            }
            if (stepId === this._NEXT_KEYWORD) {
                if (lastSelection === null) {
                    return 1;
                }
                else {
                    var lastStepId = lastSelection.stepId,
                        indexRe = /^step_(\d)$/,
                        results = indexRe.exec(lastStepId),
                        lastStepIndex = results.length > 0 ? results[1] : 0,
                        lastStepIndexInt = parseInt(lastStepIndex);
                    return lastStepIndexInt + 1;
                }
            }
        }
        return stepId;
    },

    _getStepEltById: function(stepId) {
        var selectorTemplate = new Template('.buyersGuide-questions[#{attrName}="step_#{attrValue}"]'),
            selector = selectorTemplate.evaluate({attrName: this._STEP_ID_ATTR_NAME, attrValue: stepId}),
            stepElt = $$(selector)[0];
        return stepElt;
    },

    _cleanUpPreviousStep: function() {
        var previousStep = this._previousStep,
            reelSelector = this.reelSelector,
            stepSelectButtonSelector = this.stepSelectButtonSelector,
            hiddenStepSelectButtonSelector = stepSelectButtonSelector + '.invisible',
            spinnerSelector = '.' + this._SPINNER_CLASS,
            reel = $$(reelSelector)[0],
            q = false;

        if (false === previousStep) {
            return;
        }
        reel.select(spinnerSelector).each(function(elt) { elt.remove(); });



        reel.select(hiddenStepSelectButtonSelector).each(function(elt) {
            elt.removeClassName('invisible');
        });

        if (this._ERROR_STEP_ID === previousStep) {
            this._showErrorStepElt(false);
        }

        var previousElt = this._getStepEltById(previousStep);
        if (previousElt) {
            var styleOverride = previousElt.select('.buyersGuide-select')[0].readAttribute('data-questionStyleOverride');
            if (styleOverride) {
                previousElt.removeClassName(styleOverride);
            }
        }

        q = this._getQByStepId(previousStep);
        if (q) {
            reel.removeClassName("toggle-" + q);
        }
    },

    _prepareNextStep: function(stepId, optionsToShowObj) {
        var maskObj = optionsToShowObj,
            step = this._getStepEltById(stepId);
        // hide elements as necessary
        $H(maskObj).each(function(mask) {
            this._maskOptions(step, this['_OPTION_' + mask.key.toUpperCase() + '_ATTR_NAME'], mask.value);
        }.bind(this));
    },

    _getQByStepId: function(stepId) {
        var previousStep = this._previousStep,
            targetElt = false,
            reelSelector = this.reelSelector,
            reel = $$(reelSelector)[0],
            qArr = [],
            q = false;
        targetElt = this._getStepEltById(stepId);
        qArr = targetElt.classNames().grep(/^q(\d|loading|directfit|contactus|done|nofit|error)$/);
        if (qArr.length > 0) {
            q = qArr[0];
            return q;
        }
        return false;
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
    }
});

var NoStepDisplayController = Class.create(StepDisplayControllerInterface, {
   initialize: function($super, args) {
       var _args = args || {};
   },

    moveToStep: function(stepId, optionsToShowObj) {
        console.log("moving to step " + stepId);
    },

    getStepSelections: function() {
        return [];
    },

    reset: function() {
        console.log("resetting...");
    }
});