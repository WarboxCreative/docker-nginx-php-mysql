define([
    'jquery',
    'carousel',
], function ($) {
    'use strict';

    /**
     * Quotes carousel
     * Creates a carousel for blockquotes
     *
     * @param {*} config
     * @param {*} node
     */
    function quoteCarousel(config, node) { 
        const options = {
            autoplay: config.autoplay ? true : false,
            arrows: config.arrows ? true : false,
            slidesToShow: 1,
            slidesToScroll: 1,
            fade: true,
        };

        if (config.nextArrow) {
            options.nextArrow = config.nextArrow;
        }

        if (config.prevArrow) {
            options.prevArrow = config.prevArrow;
        }

        $(node)
            .on('init', function(){
                $(this).css({
                    visibility: 'visible',
                    opacity: 1
                }).children().show();            
            })
            .slick(options);
    }

    return quoteCarousel;
});
