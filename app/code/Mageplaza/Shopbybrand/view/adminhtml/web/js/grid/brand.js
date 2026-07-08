/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'Magento_Ui/js/grid/columns/select'
], function ($, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html'
        },
        getLabel: function (record) {
            var labelTpl  = '',
                labels    = this.mp_brand_labels,
                attr      = this.mp_brand_attr,
                attrValue = 0;

            $.each(record, function(key, value) {
                if (key === attr){
                    attrValue = value;
                }
            });

            $.each(labels, function(key, value) {
                if (value['value'] === attrValue) {
                    labelTpl = value['label'];
                }
            });

            return labelTpl;
        }
    });
});
