var BuyersGuideController = Class.create(TRSCategoryBase, {

    _LOADING_STEP_ID: 'loading',
    _ROUGH_FITS_STEP_ID: 'done',
    _DIRECT_FITS_STEP_ID: 'directfit',
    _NO_FITS_STEP_ID: 'nofit',
    _ERROR_STEP_ID: 'error',
    _CONTACT_US_STEP_ID: 'contactus',

    _NEXT_KEYWORD: 'next',

    _DEFAULT_BG_CONTAINER_SELECTOR: '.buyersGuide',
    _DEFAULT_BG_CAR_INPUT_SELECTOR: '.buyersGuide-carSelect',
    _DEFAULT_BG_SUPPLEMENT_INPUT_SELECTOR: '.buyersGuide-supplement',
    _DEFAULT_GO_BUTTON_ID: 'buyersGuideStartButton',
    _DEFAULT_STOP_BUTTON_ID: 'buyersGuideStopButton',
    _DEFAULT_RESET_BUTTON_ID: 'buyersGuideResetButton',

    initialize: function($super, args) {
        var _args = args || {};
        this._moduleName = 'buyers_guide';
        this._isRunning = false;
        this.buyersGuideSelector = _args.buyersGuideSelector || this._DEFAULT_BG_CONTAINER_SELECTOR;
        this.carInputSelector = _args.carInputSelector || this._DEFAULT_BG_CAR_INPUT_SELECTOR;
        this.carSelectController = _args.carSelectController;
        this.stepDisplayController = _args.stepDisplayController;
        this.supplementInputSelector = _args.supplementInputSelector || this._DEFAULT_BG_SUPPLEMENT_INPUT_SELECTOR;
        this.goButtonId = _args.goButtonId || this._DEFAULT_GO_BUTTON_ID;
        this.stopButtonId = _args.stopButtonId || this._DEFAULT_STOP_BUTTON_ID;
        this.resetButtonId = _args.resetButtonId || this._DEFAULT_RESET_BUTTON_ID;
        this.updateCarInputsUrl = _args.updateCarInputsUrl || '';

        this.register();
        this._initializeObservers();
    },

    register: function() {
        Event.fire($$('body')[0], this.REGISTER_EVENT, this, true);
    },

    _initializeObservers: function() {
        var reelContainerSelector = this.reelContainerSelector,
            stepSelectButtonSelector = this.stepSelectButtonSelector,
            historyStepSelectButtonSelector = this.historyStepSelectButtonSelector,
            goId = this.goButtonId,
            stopId = this.stopButtonId,
            resetId = this.resetButtonId,
            resetElt = $(resetId),
            newDataEvent = this.NEW_DATA_EVENT,
            context = this;

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
    },


    moveToStep: function(stepId, optionsToShowObj) {
        this.stepDisplayController.moveToStep(stepId, optionsToShowObj);
    },


    recommendProducts: function(recommended) {
        console.log('we recommend:');
        console.log(recommended);
        this.moveToStep(this._ROUGH_FITS_STEP_ID);
    },


    _getStepEltIndex: function(stepId) {
        var steps = this._getSteps(),
            target = this._getStepEltById(stepId),
            offset = $A(steps).lastIndexOf(target);
        return offset;
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


    handleNewCatalogData: function(evt) {
        var newDom = $(evt.memo);
        var newGuideElt = newDom.select('#buyersGuideContainer')[0];
        var newActionElt = newGuideElt.select('#buyersGuideAction')[0];
        var newActionStr = newActionElt.value;
        var newActionObj = newActionStr.evalJSON();
        var newAction = newActionObj.action;
        this.takeAction(newAction);
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
        else if (command === 'id') {
            return this._parseId(remainder);
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


    _parseId: function(remainder) {
        var ids = remainder.split(',');
        console.log("here are my ids:");
        console.log(ids);
        this.recommendProducts(ids);
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