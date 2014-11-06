document.observe('dom:loaded', function() {
   trsBuyersGuide = new BuyersGuideController({
        updateCarInputsUrl: $('buyersGuideControllerData').readAttribute('data-carupdateUrl')
   });
});