document.observe('dom:loaded', function() {
   trsBuyersGuide = new BuyersGuideController({
        updateCarInputsUrl: $('buyersGuideControllerData').readAttribute('data-carupdateUrl')
   });
   jQuery( ".buyersGuide-mobileStart" ).click(function() {
        jQuery(".buyersGuide").toggleClass( "mobile-active" );
        jQuery(".buyersGuide-mobileStart").toggleClass( "mobile-active" );
	});
});


