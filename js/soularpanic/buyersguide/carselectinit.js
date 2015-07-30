document.observe('dom:loaded', function() {
    var buttonId = "buyersGuideStartButton",
        urlEltId = "buyersGuideStartUrl",
        url = $(urlEltId).readAttribute('value');
    carSelect = new CarSelectController({
        updateCarInputsUrl: $('buyersGuideControllerData').readAttribute('data-carupdateUrl')
    });

    $(buttonId).observe('click', function() {
         var carId = carSelect.getSelectedCarId();
        if (carId) {
            var params = Object.toQueryString({ car: carId });
            url = url + '?' + params;
            window.location = url;
        }
    });

});


