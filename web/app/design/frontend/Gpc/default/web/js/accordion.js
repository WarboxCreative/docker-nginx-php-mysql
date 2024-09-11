define([
    'jquery',
    'jquery.accordion',
], function ($) {
    'use strict';

    return function (config, node) {
        const $element = $(node);

        $element.accordion({
            openFirst: config.openFirst
        });
    }
});
