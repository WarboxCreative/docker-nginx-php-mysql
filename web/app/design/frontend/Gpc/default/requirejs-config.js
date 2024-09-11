var config = {
    deps: [
        'lazySizes',
        'customSelect',
        'pageMask',
    ],
    paths: {
        'carousel': 'js/plugins/slick',
        'jquery.accordion':'js/plugins/jquery.accordion',
        'lazySizes': 'js/plugins/lazysizes.min',
        'pageMask': 'js/plugins/jquery.pageMask',
        'stepper': 'js/plugins/jquery.stepper',
        'accordion': 'js/accordion',
        'customSelect': 'js/customSelect',
        'productCarousel': 'js/productCarousel',
        'categoryCarousel': 'js/categoryCarousel',
        'collapsibleOnMobile': 'js/collapsibleOnMobile',
        'quoteCarousel': 'js/quoteCarousel',
        'hideCtaBanner': 'js/hideCtaBanner',
        'owl': 'js/owlCarousel.min',
    },
    shim: {
        'jquery.accordion': {
            deps: ['jquery'],
            exports: 'jQuery.fn.Accordion',
        },
        'stepper': {
            deps: ['jquery'],
            exports: 'jQuery.fn.stepper',
        },
        'pageMask': {
            deps: ['jquery'],
            exports: 'jQuery.fn.pageMask',
        },
        'owl': {
            deps: ['jquery'],
            exports: 'jQuery.fn.owlCarousel',
        }
    }
};
