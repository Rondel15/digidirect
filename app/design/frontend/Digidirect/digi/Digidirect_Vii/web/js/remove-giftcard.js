define([
    'uiComponent',
    'jquery',
     'Magento_Customer/js/customer-data',
     'Magento_GiftCardAccount/js/action/remove-gift-card-from-quote'
    ], function (Component, $, customerData, removeAction) {
      'use strict';
      return Component.extend({
        initialize: function (config, node) {
          this._super();
          this.cardNumber = config.cardNumber;
          $(node).click(function (e) {
            e.preventDefault();
            this.removeGiftCard();
            
          }.bind(this))
        },
        removeGiftCard: function () {
          var cartData = customerData.get('cart')();
          cartData['data_id'] = 0;
          customerData.set('cart', cartData);
          
          removeAction(this.cardNumber);
          
          location.reload();
        }
      });
    });