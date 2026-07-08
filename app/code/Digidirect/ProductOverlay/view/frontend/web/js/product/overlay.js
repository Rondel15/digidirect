define([
    'Magento_Ui/js/grid/columns/column',
    'Magento_Catalog/js/product/list/column-status-validator'
], function (Column, columnStatusValidator) {
    'use strict';

    return Column.extend({
        /**
         * Check if component must be shown.
         *
         * @return {Boolean}
         */
        isAllowed: function () {
            return columnStatusValidator.isValid(this.source(), 'overlay', 'show_attributes');
        },

        itemOverlays: function (row) {
            var extensionAttributes = row['extension_attributes'];
            if (extensionAttributes && extensionAttributes['overlays']) {
                return extensionAttributes['overlays'];
            }
            return [];
        },

        getContainerPath: function (overlay) {
            return overlay['container_path'];
        },

        getDataMageInit: function (overlay) {
            return {
                'productOverlay': {
                    'size': this.getImageSize(overlay),
                    'path': this.getContainerPath(overlay),
                    'mode': overlay['mode'],
                    'hideForConfigurable': overlay['hide_in_configurable']
                }
            };
        },

        getImageSize: function (overlay) {
            return overlay['cat_image_size'];
        },
        hasImageSize: function (overlay) {
            return this.getImageSize(overlay) ? ' -has-size' : '';
        },

        getNativeText: function (overlay) {
            var div = document.createElement('div');
            div.innerHTML = overlay.cat_txt;

            return div.textContent || div.innerText || '';
        },

        getUseForParent: function (overlay) {
            if (parseInt(overlay['use_for_parent'], 10)) {
                return 'use-for-parent';
            }
            return undefined;
        }
    });
});
