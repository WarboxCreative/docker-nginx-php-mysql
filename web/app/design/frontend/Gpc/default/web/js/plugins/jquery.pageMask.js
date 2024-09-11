(function($) {
    'use strict';

    $.fn.pageMask = function(options) {
        return this.each(function() {
            init($(this), options);
        });
    };

    /**
     * Init
     */
    function init($pageMask, options) {
        createOverlay();

        if (typeof options !== 'undefined' && options.activeStates) {
            bindWatchers($pageMask, options);
        }
    }

    /**
     * @param {*} $pageMask
     * @param {*} options
     */
    function bindWatchers($pageMask, options) {
        const elToWatch   = (typeof($pageMask.attr('id')) !== 'undefined' || $pageMask.attr('id') !== null) ? '#' + $pageMask.attr('id') :  '.' + $pageMask.attr('class');
        const targetNodes = document.querySelectorAll(elToWatch);
        const observer    = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                let shouldShowMask = false;

                options.activeStates.map(function(activeState) {
                    if (mutation.target.matches(activeState)) {
                        shouldShowMask = true;
                    }
                });

                if (shouldShowMask) {
                    maskIn(options);
                } else {
                    maskOut(options);
                }
            });
        });

        const config = {
            attributes: true
        };

        targetNodes.forEach(function(selectedTarget) {
            observer.observe(selectedTarget, config);
        });
    }

    function createOverlay() {
        if (!$('.o-overlay').length) {
            $('body').append('<div class="o-overlay"></div>');
        }
    }

    /**
     * @param {*} options
     */
    function maskIn(options) {
        const $overlay = $('.o-overlay');

        if (typeof options !== 'undefined' && options.additionalClasses && Array.isArray(options.additionalClasses)) {
            options.additionalClasses.map(function(additionalClass) {
                $overlay.addClass(additionalClass);
            });
        }

        $('body').addClass('o-overlay--active');

        $overlay.fadeIn('fast');
    }

    /**
     * @param {*} options
     */
    function maskOut(options) {
        const $overlay = $('.o-overlay');

        if (typeof options !== 'undefined' && options.additionalClasses && Array.isArray(options.additionalClasses)) {
            options.additionalClasses.map(function(additionalClass) {
                $overlay.removeClass(additionalClass);
            });
        }

        $overlay.fadeOut('fast', function() {
            $('body').removeClass('o-overlay--active');
        });
    }
}(jQuery));
