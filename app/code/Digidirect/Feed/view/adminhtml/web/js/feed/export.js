define([
    'jquery',
    'jquery/ui',
    'uiLayout',
    'uiComponent',
    'uiRegistry'
], function ($, ui, layout, Component, Registry) {
    'use strict';

    return Component.extend({
        defaults: {
            mode: 'new',
            exportUrl: null,
            id: null
        },

        generate: function () {
            var self = this;

            var progress = Registry.get('feed_export.progress');

            progress.show('show');

            $.ajax(this.exportUrl, {
                method: 'GET',
                data: {
                    id: self.id,
                    mode: self.mode,
                    rand: Math.random()
                },

                complete: function (response) {
                    if (self.mode == 'new') {
                        progress.observeExport();
                    }

                    try {
                        var json = $.parseJSON(response.responseText);

                        if (json.success) {
                            if (json.status == 'completed') {
                                progress.mute();
                                progress.setProgress(json.progress);
                                self.mode = 'new';
                            } else {
                                progress.setProgress(json.progress);
                                self.mode = 'continue';
                                self.generate();
                            }
                        } else {
                            progress.mute();
                            progress.setProgress(json.progress);
                            self.mode = 'new';
                        }
                    } catch (error) {
                        progress.mute();
                        progress.setProgress({error: response.responseText});
                        self.mode = 'new';
                    }
                }
            });
        }
    });
});
