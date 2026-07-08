/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
        'uiElement',
        'mage/translate'
], function (Element, $t) {
    'use strict';

    var DEFAULT_GROUP_ALIAS = 'default';

    return function (target) {
        return target.extend({
            defaults: {
                alias: DEFAULT_GROUP_ALIAS,
                title: $t('Payment Information'),
                sortOrder: 100,
                displayArea: 'payment-methods-items-${ $.alias }'
            }
        });
    }
});
