## Digidirect Single Checkout Page

./view/single-button.js

Option | Type | Default | Description
------ | ---- | ------- | -----------
template | string | 'Digidirect_SingleCheckoutButton/button' | Template for component
labelButtonShippingStep | string | 'Next' | Label for button on shipping step
labelButtonPaymentStep | string | 'Place order' | Label for button on payment step
isOnlyPaymentStep | boolean | false | Disable or enable custom button on shipping step
shippingFormSelector | string | '#co-shipping-method-form' | Selector of shipping form
paymentMethodContainer | string | '.payment-method' | Selector of payment method container
defaultButton | string | '.action.primary' | Selector for default button of payment method

./view/error-methods.js

Option | Type | Default | Description
------ | ---- | ------- | -----------
template | string | 'Digidirect_SingleCheckoutButton/error-methods' | Template for component
errorPaymentMethodText | string | 'Please, select payment method' | Error text