## Product Overlay Settings

Option | Type | Default | Description
------ | ---- | ------- | -----------
size | string | 20 | Percent value of the container width specified in "path"
path | string | '.product-image-container' | Selector of element where should be appended overlay
mode | string | 'cat' | Type of page: 'cat' - category/search results or 'prod' - product details page
productGridSelector | string | '.product-item' | The container to find DOM-selector from back-end (admin panel) to append overlay.
hideForConfigurable | boolean | false | Hide for configurable product
swatchContainer | string | [data-role=swatch-options] | Selector with swatches 
overlaySelector | string | '' | Selector for current overlay 
