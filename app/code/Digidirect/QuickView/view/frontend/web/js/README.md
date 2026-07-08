## Quick View Settings
quickview.js

Option | Type | Default | Description
------ | ---- | ------- | -----------
button | string | '[data-role=quickview-button]' | Selector Quick View button
contentContainer | string | '[data-role=quickview-content]' | Selector wrapper of Quick View iframe
iframe | string | '[data-role=quickview-window]' | Selector Quick View iframe
itemSelector | string | '[data-container=product-grid]' | Selector product wrapper
mobileListener | string | '.product-item-photo' | Selector link when clicking on which will open popup (for mobile version)
modal | object | {type: 'popup', buttons: []} | Options for modal widget http://devdocs.magento.com/guides/v2.1/javascript-dev-guide/widgets/widget_modal.html
productForm | string | '#product_addtocart_form' | Selector form on PDP Page


quickview-product-view.js

Option | Type | Default | Description
------ | ---- | ------- | -----------
openInNewWindow | boolean | true | Upload all data to a new window when possible
compareLink | string | 'compareLink' | Class name of comparebutton
eventElements | string | '[data-post]' | Selector for elements of events
redirectLinks | string | '.mailto' | A list of links that, when clicked, need to do the redirection of the parent window. If there are several links, you must specify a comma

