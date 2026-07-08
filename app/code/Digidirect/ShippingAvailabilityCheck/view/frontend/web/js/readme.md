## Digidirect ShippingAvailabilityCheck

shipping-availability.js

Option | Type | Default | Description
------ | ---- | ------- | -----------
product_id | number | null | Product ID 
country_id | string | '#country' | Selector for dropdown of country
region_id | string | '[data-role="region_id"]' | Selector for dropdown of region
postcode | string | '[data-role="postcode"]' | Selector for field of poscode
customer_id | number | null | Customer ID
button | string | '[data-role="check-shipping-methods"]' | Selector for button of check shipping methots
superAttributeElement | string | '.super-attribute-select' | Selector for elements of super attributes
superAttributeWrapper | string | '.swatch-attribute' | Selector for container of super attribute
superAttributeId | string | 'attribute-id' | Super attribute indificator
shippingMethodsContainer | string | '[data-role="shipping-methods"]' | Selector target element for insert shipping methods
emptyShippingListMessage | string | {'There is no available shipping methods for the entered address'} | Message for case when aren't available shipping methots
fieldsForValidate | string | '#country' | List of required fields. If need validate more than one, so separete fields by comma ('#country, [data-role="region_id"], [data-role="postcode"]')
validateClass | string | 'required-entry' | Validate class
validatePostcodeMessage | string | 'Please enter a valid zip code.' | Error message for zip code field.
addToCartButton | string | '#product-addtocart-button' | Selector for button of add to cart form
productType | string | '' | Type of product
isInputsNeeded | boolean | flag to check if need input fields for shipping methods
grouped |  | object |  | Selectors for grouped products
 | productList | string | '#super-product-table' | Selector for container with products  
 | qtyNameMask | string | '[name="super_group[&]"]' | Mask for find qty field for simple product
 | productItem | string | '[data-product-id]' | Selector for product item
 | productIdAttribute | string | 'data-product-id' | Data attribute with id of simple product
 bundle |  | object |  | Selectors for bundle products
 | container | string | '.bundle-options-wrapper' | Selector for container with products 
 | field | string | '[name^="bundle_option"]' | Selector for option fields
 giftCard |  | object |  | Selectors for gift cards
 | amountField | string | '#giftcard-amount-input' | Selector for amount field
 | fieldsContainer | string | '[data-container-for="giftcard_info"]' | Selector for container with required fields
 | requiredFields | string | '.required-entry'| Selector for required fields
shipping-region-updater.js

Option | Type | Default | Description
------ | ---- | ------- | -----------
isSpecifiedFieldsRequired | boolean | true | Setting for disable default validation for fields 