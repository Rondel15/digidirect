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

var config = {
    paths: {
        daterangepicker: 'Mageplaza_Shopbybrand/js/lib/daterangepicker.min',
        chartBundle: 'Mageplaza_Shopbybrand/js/lib/Chart.bundle.min',
        initDateRange: 'Mageplaza_Shopbybrand/js/lib/initDateRange',
    },
    map: {
        '*': {
            moment: 'Mageplaza_Shopbybrand/js/lib/moment.min'
        }
    },
    shim: {
        'Mageplaza_Shopbybrand/js/lib/chartjs-plugin-labels':{
            deps :['Mageplaza_Shopbybrand/js/lib/Chart.bundle.min']
        }
    }
};
