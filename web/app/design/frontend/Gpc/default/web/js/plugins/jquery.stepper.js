;(function ($) {
    $.fn.stepper = function(options) {

        const defaults = {
            countBy: 1,
            decimalPlaces: 0
        };

        const settings = $.extend({}, defaults, options);

        return this.each(function() {
            const $stepper = $(this);

            if (!$stepper.data('stepper-init')) {
                let updatedVal;

                const $stepUp = $('<span/>', {
                    class: 'o-stepper__button o-stepper__button--up',
                    html: '+',
                    click: function() {
                        updatedVal = (Number($stepper.val()) + settings.countBy).toFixed(settings.decimalPlaces);
                        $stepper.val(updatedVal);
                    }
                });

                const $stepDown = $('<span/>', {
                    class: 'o-stepper__button o-stepper__button--down',
                    html: '&ndash;',
                    click: function() {
                        updatedVal = (Number($stepper.val()) - settings.countBy).toFixed(settings.decimalPlaces);

                        if (updatedVal >= 0) {
                            $stepper.val(updatedVal);
                        } else {
                            $stepper.val(0);
                        }
                    }
                });

                if (!$stepper.val()) {
                    $stepper.val(0);
                }

                $stepper.addClass('o-stepper__input');

                if (!$stepper.parent().hasClass('input-group') ) {
                    $stepper.wrap('<div class="o-stepper"></div>');
                } else {
                    $stepper.parent().wrap('<div class="o-stepper"></div>');
                }

                if ($stepper.parent().hasClass('input-group')) {
                    $stepper.parent().before($stepDown);
                    $stepper.parent().after($stepUp);
                } else {
                    $stepper.before($stepDown);
                    $stepper.after($stepUp);
                }

                $stepper.data('stepper-init', true);
            }
        });
    };
})(jQuery);
