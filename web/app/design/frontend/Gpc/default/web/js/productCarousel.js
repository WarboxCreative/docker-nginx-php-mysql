define([
    'jquery',
    'carousel',
], function ($) {
    'use strict';

    /**
     * @param {*} config
     * @param {*} node
     */
    function productCarousel(config, node) {
        const $carousel = $(node);

        $carousel
            .on('init', function(){
                $(this).css({
                    visibility: "visible",
                    opacity: 1
                }).children().show();
            })
            .slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: true,
                dots: true,
                infinite: true,
                mobileFirst: true,
                responsive: [
                    {
                        breakpoint: 767,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2,
                        }
                    },
                    {
                        breakpoint: 993,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3,
                            arrows: false,
                        }
                    }
                ]
            });
    }

    return productCarousel;
});
