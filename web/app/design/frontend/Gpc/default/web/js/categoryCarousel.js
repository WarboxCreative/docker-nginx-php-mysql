define([
    'jquery',
    'carousel',
], function ($) {
    'use strict';

    /**
     * @param {*} config
     * @param {*} node
     */
    function categoryCarousel(config, node) {
        const $carousel = $(node);

        const $pagination = $('<div class="c-category__pagination"></div>')
        $carousel.after($pagination);

        $carousel
            .on('init', function(){
                $(this).css({
                    visibility: "visible",
                    opacity: 1
                }).children().show();
            })
            .slick({
                slidesToShow: 2,
                slidesToScroll: 2,
                arrows: false,
                dots: true,
                adaptiveHeight: true,
                infinite: false,
                mobileFirst: true,
                appendDots: $pagination,
                responsive: [
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 4,
                            slidesToScroll: 4,
                            arrows: true,
                        }
                    },
                    {
                        breakpoint: 992,
                        settings: {
                            slidesToShow: 5,
                            slidesToScroll: 5,
                            arrows: true,
                        }
                    }
                ]
            });
    }

    return categoryCarousel;
});
