document.observe('dom:loaded', function() {
    carSelectController = new CarSelectController({
        updateCarInputsUrl: $('buyersGuideControllerData').readAttribute('data-carupdateUrl')
    });
   trsBuyersGuide = new BuyersGuideController({
       carSelectController: carSelectController
   });
   jQuery( ".buyersGuide-mobileStart" ).click(function() {
        jQuery(".buyersGuide").toggleClass( "mobile-active" );
        jQuery(".buyersGuide-mobileStart").toggleClass( "mobile-active" );
	});
});


