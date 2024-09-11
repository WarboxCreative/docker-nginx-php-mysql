define([
    'jquery',
], function ($) {
    'use strict';

    return function(config, node) {        
        const $el = $(node);

        // Check if the banner should be shown
        if (localStorage.getItem('CtaBanner') !== 'hidden') {
            $el.slideDown();
        }

        $('#ctaBannerClose').on('click', function() {
            // Store in local storage so that the banner doesn't show again
            localStorage.setItem('CtaBanner', 'hidden');

            // Hide the banner
            $el.slideUp();
        });
    }
});