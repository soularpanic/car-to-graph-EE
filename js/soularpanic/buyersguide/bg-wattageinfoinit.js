document.observe('dom:loaded', function() {
    jQuery('.wattageInfoButton').fancybox({
        autoSize: true,
        autoCenter: true,
        minWidth: 475,
        maxWidth: 500,
        minHeight: 290,
        maxHeight: 500,
        width: '80%',
        height: '70%',
        padding: 5
    });
});