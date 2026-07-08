define([
  'uiComponent',
  'jquery',
  'ko',
  'underscore',
  'Magento_Customer/js/customer-data'
], function (Component, $, ko, _, customerData) {
  'use strict';
  return Component.extend({
    defaults: {
      template: 'Digidirect_ShippingAvailabilityCheck/sdd-checker',
      isSddAvailable: ko.observable(false),
      isCheckSddShipping: ko.observable(false),
      isAvailableData: ko.observable({}),
      productPrefix:'',
      restUrl: '',
      cmsSddInfo:'',
      cmsSddBoxTitle:''
      },
    initialize: function () {
      this._super();
      this.bindAllEvent();
    },

    bindAllEvent: function () {
      $(document).on('mystore.save.success', function () {
        this.getDataFromServer();
      }.bind(this));
      this.getDataFromServer();
      this.isAvailableData.subscribe(function (data) {
        this.checkAvailableData(data);
      }.bind(this));
      $(document).on('click', '#check_sdd', function (event, data) {
        $('.mystore-button').click();
      });
      $(document).on('click', '.current-store-link', function () {
          $('.mystore-button').click();
      });
      $(document).on('mouseover', '.sdd-title-info-icon', function () {
        $('.cms-sdd-info-block-modal-data').css('display', 'block');
      });
      $(document).on('mouseleave', '.sdd-title-info-icon', function () {
        $('.cms-sdd-info-block-modal-data').css('display', 'none');
      });
    },

    checkAvailableData: function (data) {
      if (_.isObject(data) && data['sddCheckResult'] !== null) {
        this.isCheckSddShipping(true);
        if (!_.isUndefined(data['sddCheckResult'][this.productPrefix])
            && !_.isNull(data['sddCheckResult'][this.productPrefix])) {
          this.isSddAvailable(data['sddCheckResult'][this.productPrefix]);
        }
        return true;
      }
      this.isCheckSddShipping(false);
      this.isSddAvailable(false);
    },

    getDataFromServer: function () {
      $.getJSON(this.restUrl + this.productPrefix)
          .done(function (data) {
            var responseObj = JSON.parse(data);
            if (_.has(responseObj, 'error')) {
              console.log(data);
              return;
            }
            this.checkAvailableData(responseObj);
          }.bind(this))
          .fail(function (response) {
          })
    }
  });
});
