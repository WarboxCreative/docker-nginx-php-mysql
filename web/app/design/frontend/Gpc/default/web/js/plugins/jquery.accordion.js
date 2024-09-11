(function($) {
    'use strict';

    const methods = {
        destroy: function () {
            const $accordion = $(this);

            $accordion.find('> .c-accordion__item > [data-accordion-link]').unbind('click');

            removeAccessibility($accordion);
            removeMarkup($accordion);
        }
    };

    $.fn.accordion = function(options) {
        if (methods[options]) {
            return methods[options].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof options === 'object' || !options) {
            return this.each(function() {
                const el = $(this);
                return init(el, options);
            });
        } else {
            $.error( 'Method ' +  options + ' does not exist on accordion' );
        }
    };

    function init($accordion, options) {
        markupAccordion($accordion);
        bindEvents($accordion);

        if (typeof options !== 'undefined' && options.openFirst) {
            openFirst($accordion);
        }
    }

    /**
     * @param {any} $accordion
     */
    function bindEvents($accordion) {
        $accordion.find('> .c-accordion__item > [data-accordion-link]').on('click', function(e) {
            e.preventDefault();

            const $selectedContent = $accordion.find('> .c-accordion__item > [data-accordion-content="' + $(this).attr('data-accordion-link') + '"]');

            if (!$selectedContent.hasClass('--active')) {
                $accordion.find('> .c-accordion__item > [data-accordion-content]')
                    .removeClass('--active')
                    .attr('aria-hidden', true);

                $accordion.find('> .c-accordion__item > [data-accordion-content]').slideUp(350);

                $accordion.find('> .c-accordion__item > [data-accordion-link]')
                    .removeClass('--active')
                    .attr('aria-expanded', false)
                    .attr('aria-selected', false);

                $(this)
                    .addClass('--active')
                    .attr('aria-expanded', true)
                    .attr('aria-selected', true);

                $selectedContent
                    .addClass('--active')
                    .attr('aria-hidden', false)
                    .slideToggle();

                if (typeof options !== 'undefined' && options.scrollToContent) {
                    let linkPosition = $selectedContent.offset().top;

                    if (typeof options !== 'undefined' && options.fixedHeader) {
                        if (window.innerWidth > 992) {
                            linkPosition = $selectedContent.offset().top - $('.page-header').height()
                        }
                    }

                    $('html, body').animate({
                        scrollTop: linkPosition
                    }, 2000);
                }
            }
            else {
                $(this).removeClass('--active');
                $selectedContent.removeClass('--active');
                $selectedContent.slideToggle();
            }
        });
    }

    /**
     * @param {any} $accordion
     */
    function markupAccordion($accordion) {
        $accordion
            .addClass('c-accordion')
            .attr('role', 'tablist');

        $accordion
            .children()
            .addClass('c-accordion__item');

        $accordion
            .find('> .c-accordion__item > [data-accordion-link]')
            .addClass('c-accordion__link')
            .attr('role', 'tab')
            .attr('aria-expanded', false)
            .attr('aria-selected', false);

        $accordion
            .find('> .c-accordion__item > [data-accordion-content]')
            .addClass('c-accordion__content')
            .attr('role', 'tabpanel')
            .attr('aria-hidden', true);
    }

    /**
     * @param {any} $accordion
     */
    function openFirst($accordion) {
        $accordion.find('> .c-accordion__item > [data-accordion-link]')
            .first()
            .addClass('--active')
            .attr('aria-expanded', true)
            .attr('aria-selected', true);

        $accordion.find('> .c-accordion__item > [data-accordion-content]')
            .first()
            .addClass('--active')
            .attr('aria-hidden', false);

        $accordion.find('> .c-accordion__item > [data-accordion-content]').first().slideToggle();
    }

    /**
     * @param {*} $accordion
     */
    function removeMarkup($accordion) {
        const $accordionLinks = $accordion.find('> .c-accordion__item > [data-accordion-link]');
        const $accordionContent = $accordion.find('> .c-accordion__item > [data-accordion-content]')
        const $accordionItems = $accordion.children();

        $accordionLinks.removeClass('c-accordion__link');
        $accordionContent.removeClass('c-accordion__content');
        $accordionItems.removeClass('c-accordion__item');
        $accordion.removeClass('c-accordion');
    }

    /**
     * @param {*} $accordion
     */
    function removeAccessibility($accordion) {
        $accordion
            .removeAttr('role');

        $accordion
            .find('> .c-accordion__item > [data-accordion-link]')
            .removeAttr('role')
            .removeAttr('aria-expanded')
            .removeAttr('aria-selected');

        $accordion
            .find('> .c-accordion__item > [data-accordion-content]')
            .removeAttr('role')
            .removeAttr('aria-hidden')
            .removeAttr('style');
    }

}(jQuery));
