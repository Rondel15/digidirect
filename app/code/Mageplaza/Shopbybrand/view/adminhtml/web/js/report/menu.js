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
    'moment',
    'uiRegistry',
    'Magento_Catalog/js/price-utils',
    'daterangepicker',
    'Mageplaza_Shopbybrand/js/lib/Chart.bundle.min',
    'Mageplaza_Shopbybrand/js/lib/chartjs-plugin-labels'
], function ($, moment, uiRegistry, priceUtils) {
    'use strict';
    var dateRangeEl = $('#daterange');

    $.widget('mageplaza.menu', {
        _create: function () {
            var self      = this,
                dateFomat = self.options.date;

            this.initNowDateRange(moment(dateFomat[0]), moment(dateFomat[1]));
            this.initDateRangeApply(dateFomat[0], dateFomat[1]);

            $('body').on('click', function (e) {
                var dateRangElement = $('#daterange');

                if ($('.daterangepicker').is(':visible')) {
                    $('.drp-calendar.left').show();
                    $('.drp-calendar.right').show();
                }

                if ($(e.target).parents().hasClass('daterangepicker')) {
                    if (!$('.daterangepicker').is(':visible')) {
                        if ($(e.target).hasClass('cancelBtn')) {
                            self.initDateRangeApply(dateRangElement.data().startDate.format('YYYY-MM-DD HH:mm:ss'), dateRangElement.data().endDate.format('YYYY-MM-DD HH:mm:ss'));
                        }else{
                            self.initDateRangeApply(dateRangElement.data().mp_startDate, dateRangElement.data().mp_endDate);
                        }
                        $('.drp-calendar.left').show();
                        $('.drp-calendar.right').show();
                    }
                }
            });
        },

        initDateRange: function (el, start, end, data) {
            function cb (cbStart, cbEnd) {
                el.find('span').html(cbStart.format('MMM DD, YYYY') + ' - ' + cbEnd.format('MMM DD, YYYY'));
                el.data()['mp_startDate'] = cbStart.format('YYYY-MM-DD HH:mm:ss');
                el.data()['mp_endDate']   = cbEnd.format('YYYY-MM-DD HH:mm:ss');
            }

            el.daterangepicker(data, cb);
            cb(start, end);
        },

        initNowDateRange: function (start, end) {
            var dateRangeData = {
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [
                        moment().subtract(1, 'month').startOf('month'),
                        moment().subtract(1, 'month').endOf('month')
                    ],
                    'YTD': [moment().subtract(1, 'year'), moment()],
                    '2YTD': [moment().subtract(2, 'year'), moment()]
                }
            };

            this.initDateRange(dateRangeEl, start, end, dateRangeData);
        },

        initDateRangeApply: function (start, end) {
            var self = this,
                dateRange = [start, end, null, null];

            $.ajax({
                url: self.options.gridUrl,
                type: 'POST',
                data: {dateRange: dateRange},
                showLoader: true,
                success: function (res) {
                    if (res.success) {
                        $('#mpbrand-report-grid').html(res.success);
                        $('#mpbrand-report-grid').trigger('contentUpdated');

                        self.getItemData(dateRange);
                    }
                },
                error: function (res) {
                    $('.mpshopbybrand-messages').html(res.responseText);
                }
            });
        },

        getItemData: function (dateRange) {
            var self = this;

            $.ajax({
                url: self.options.itemDataUrl,
                type: 'POST',
                data: {dateRange: dateRange},
                success: function (res) {
                    if (res.success) {
                        if ($('#mpbrand-report-chart').length) {
                            self.buildChart(res.success);
                        }
                    }
                },
                error: function (res) {
                    $('.mpshopbybrand-messages').html(res.responseText);
                }
            });
        },

        buildChart: function (data) {
            var items          = data.items,
                showBrandChart = false,
                self           = this,
                brandIndex     = 0,
                brandData      = [];

            if (Object.keys(items).length !== 0) {
                _.each(items, function (record) {
                    self.createChartData(brandData, record, brandIndex);
                    brandIndex++;
                    if (record.total > 0) {
                        showBrandChart = true;
                    }
                });

                if (showBrandChart) {
                    brandData.sort(function (a, b) {
                        return Number(b.data) - Number(a.data);
                    });
                    brandData = _.filter(brandData, function (value, index) {
                        return index < 999;
                    });
                    this.createChart(this.getChartData(brandData), 'mpbrand-chart');
                }

                $('#mpbrand-report-chart').attr('style', 'display: inline-block');
                this.showChart('brand', showBrandChart);
            }else {
                $('#mpbrand-report-chart').attr('style', 'display: none');
            }
        },

        getChartData: function (data) {
            return {
                data: _.pluck(data, 'data'),
                labelColor: {
                    colors: _.pluck(data, 'color'),
                    labels: _.pluck(data, 'label')
                },
                priceFormat: this.options.priceFormat
            };
        },

        showChart: function (brandType, isShow) {
            if (isShow) {
                $('.dashboard-item-content-' + brandType + '-chart').show();
            } else {
                $('.dashboard-item-content-' + brandType + '-chart').hide();
            }
        },

        createChartData: function (chartData, record, index) {
            var pieColors = ['#ff1500', '#ffbf00', '#641f6f', '#00a10e', '#0058e6', '#8e6cef', '#8399eb', '#007ed6',
                    '#97d9ff', '#5fb7d4', '#7cdddd', '#26d7ae', '#2dcb75', '#1baa2f', '#52d726', '#d5f30b', '#ffec00', '#ffaf00'],
                color     = index < 18 ? pieColors[index] : this.randomColor();

            chartData.push({data: record.total, color: color, label: record.attribute_name});
        },

        randomColor: function () {
            return '#' + (0x1000000 + Math.random() * 0xffffff).toString(16).substr(1, 6);
        },

        createChart: function (chartData, chartElement) {
            var data = {
                type: 'pie',
                data: {
                    labels: chartData.labelColor.labels,
                    datasets: [
                        {
                            data: chartData.data,
                            fill: false,
                            backgroundColor: chartData.labelColor.colors,
                            borderWidth: 1,
                            label: ''
                        }
                    ]
                },
                options: {
                    legend: {
                        display: true,
                        position: 'right'
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    tooltips: {
                        callbacks: {
                            label: function (tooltipItem, data) {
                                var dataset = data.datasets[tooltipItem.datasetIndex];
                                var index   = tooltipItem.index;

                                return data.labels[index] + ': ' + (chartData.priceFormat ? priceUtils.formatPrice(dataset.data[index], chartData.priceFormat) : dataset.data[index]);
                            }
                        }
                    },
                    plugins: {
                        labels: {
                            render: 'percentage',
                            precision: 2,
                            fontColor: '#ffffff'
                        }
                    }
                }
            };

            if (typeof window[chartElement] !== 'undefined' && typeof window[chartElement].destroy === 'function') {
                window[chartElement].destroy();
            }

            window[chartElement] = new Chart($('#' + chartElement), data);
        }
    });

    return $.mageplaza.menu;
});
