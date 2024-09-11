define([
    'jquery',
    'jquery.accordion'
], function($) {
    $(function () {
        $('.js-product-details').accordion({
            openFirst: true
        }).fadeIn(600);
    });
});
