# Digidirect Click & Collect

collect-block.js

Option | Type | Default | Description
------ | ---- | ------- | -----------
collectBlock | string | '[data-role="collect-block"]' | The selector for the element for changing visibility
triggerElement | string | '[data-role="trigger-collect"]' | The selector for event element
visibleClass | string | '-visible' | Class for element visibility

collect-cart.js

Option | Type | Default | Description
------ | ---- | ------- | -----------
storeName | string | '[data-role="collect-store"]' | The selector element for replace store's name
collectTrigger | string | '[data-role="collect-trigger"]' | The selector element for open popup
deliveryTrigger | string | '[data-role="delivery-trigger"]' | The selector element for to set delivery type
dataActionr | string | '' | Action url
collectPlaceId | string | '' | Store place id
productId | string | '' | Product id
quoteItemId | string | '' | Quote id
deliveryInstead | string | '' | Url for to set delivery type
collectModal | string | '#collect_modal_cart' | The selector element for to call popup 

collect-modal.js

Option | Type | Default | Description
------ | ---- | ------- | -----------
collectModal | string | '[data-role="collect-modal"]' | The selector element for to create popup
modalTitle | string | 'Find collect store place' | The title for popup
modalButtonText | string | 'Buy and Collect' | The button label
modalButtonClass | string | 'action-primary' | The button class
collectPlace | string | '#cart_collect_place_id' | The selector for input element of store place id
collectProduct | string | '#collect_product_id' | The selector for input element of product id
collectQuote | string | '#collect_quote_item_id' | The selector for input element of quote item id
collectStoreInput | string | '[name="collect_place_id"]' | The selector of checkbox element for to choose store
collectDeliveryTypeInput | string | '[data-role="trigger-collect"]' | The selector of checkbox element for choose "Delivery" or "Click and Collect"
collectItem | string | 'collect-item_' | Beginning of the class name to generate the selector for the product item for which the popup was called
