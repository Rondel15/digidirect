define([
    'jquery',
    'domReady!',
    'mage/translate',
    'Magento_Checkout/js/view/shipping',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/shipping-save-processor',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/quote',
    'uiRegistry',
    'mage/validation',
    'Magento_Customer/js/model/customer'
], function (
    $,
    domReady,
    $t,
    ShippingComponent,
    stepNavigator,
    shippingSaveProcessor,
    shippingService,
    quote,
    registry,
    validation,
    customer
) {
    'use strict';

    function toggleShippingMethod() {
        $('#checkoutSteps li').removeClass('active').addClass('inactive');
        $('.opc-wrapper .step-content').hide();
        $('.opc-wrapper li .action-extension-toolbar').hide();

        if (!customer.isLoggedIn()) {
            $('#checkoutSteps li#customer-info').removeClass('inactive').addClass('active');
            $('#checkoutSteps li#customer-info .step-content').show();
            $('#checkoutSteps li#customer-info').css('border', 'none');
            $('#checkoutSteps  li#customer-info .action-extension-toolbar').show();
        }

        $('#checkoutSteps li#shipping').removeClass('inactive').addClass('active');
        $('#checkoutSteps li#shipping .step-content').show();
        $('#checkoutSteps li#shipping').css('border', 'none');
        $('#checkoutSteps  li#shipping .action-extension-toolbar').show();

        if ($('input[name="delivery_type"]:checked').val() == 'delivery') {
            $('#checkoutSteps li#opc-shipping_method').removeClass('inactive').addClass('active');
            $('#checkoutSteps li#opc-shipping_method .step-content').show();
            $('#checkoutSteps li#opc-shipping_method').css('border', 'none');
            $('#checkoutSteps  li#opc-shipping_method .action-extension-toolbar').show();

            $('#checkoutSteps  .shipping-methods li').removeClass('inactive').addClass('active');

            $('#checkout-step-shipping .collect-block').hide();
        } else {
            $('#payment .step-title.accordion-step').text('3. Payment');
            $('#checkoutSteps li#payment').removeClass('inactive').addClass('active');
            $('#checkoutSteps li#payment .step-content').show();
            $('#checkoutSteps li#payment').css('border', 'none');
            $('#checkoutSteps  li#payment .action-extension-toolbar').show();

            $('#checkoutSteps li#payment #latipay-form li.latipay-options-item').removeClass('inactive').addClass('active');
        }

    }

    $(document).on("click", "#delivery-info-button-extension", function () {

        var shippingView = registry.get('checkout.steps.shipping-step.shippingAddress');

        if (shippingView && shippingView.validateShippingAddress()) {
            toggleShippingMethod();
        } else {
            console.warn("Shipping view not available or validation failed.");
        }
    });
});
