define([
    'jquery',
    'matchMedia',
], function ($, mediaCheck) {
    'use strict';

    return function(config, node) {        
        let $collapsible = null;
        const targetElement = config.targetElement;
        const openElement = config.openElement;

        mediaCheck({
            media: 'screen and (max-width:767px)',
            entry: function () {
                $collapsible = $(targetElement).collapsible({
                    animate: { 
                        duration: 350,
                        easing: 'swing',
                    }
                });
                    
                if (openElement) {
                    $(openElement).collapsible('activate');
                }
            },
            exit: function () {
                if ($collapsible) {
                    // Restore visibility of content
                    if($collapsible.collapsible) {
                        $collapsible.collapsible('forceActivate');
                        $collapsible.collapsible('destroy');
                    }
                }
            }
        });
    }
});