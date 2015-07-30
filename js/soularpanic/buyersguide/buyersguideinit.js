document.observe('dom:loaded', function() {
    carSelectController = new CarSelectController({
        updateCarInputsUrl: $('buyersGuideControllerData').readAttribute('data-carupdateUrl')
    });
    stepDisplayController = new QSlideStepDisplayController({

    });
   trsBuyersGuide = new BuyersGuideController({
       carSelectController: carSelectController,
       stepDisplayController: stepDisplayController
   });
   jQuery( ".buyersGuide-mobileStart" ).click(function() {
        jQuery(".buyersGuide").toggleClass( "mobile-active" );
        jQuery(".buyersGuide-mobileStart").toggleClass( "mobile-active" );
	});
});


