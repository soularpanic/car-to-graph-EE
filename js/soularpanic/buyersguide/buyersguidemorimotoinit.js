document.observe('dom:loaded', function() {
    carSelectController = new CarSelectController({
        updateCarInputsUrl: $('buyersGuideControllerData').readAttribute('data-carupdateUrl')
    });
    stepDisplayController = new NoStepDisplayController({});
    trsBuyersGuide = new BuyersGuideController({
        carSelectController: carSelectController,
        stepDisplayController: stepDisplayController
    });
});


