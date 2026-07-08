## Layered Navigation Settings

Option |Extra| Type | Default | Description
------ |-----| ---- | ------- | -----------
triggerApplyButton || boolean | false | Refresh the product collection by clicking on the "Apply" button 
triggerApplyMode || boolean | false | As 'triggerApplyButton' but for devices with the width of the screen less than specified in 'applyModeBreakpoint' 
applyModeBreakpoint || string | '(max-width: 769px)' | Defines when should be enable Apply Mode
enableAjax || boolean | false | Refresh the product collection via AJAX
enabledSeoUrls || boolean | false | Enable SEO URLs
postfix || string | '' | Postfix for SEO URLs
baseUrl || string | '' | Base URL for category
selectors || object | {see below} | Object of selectors
- | applyButton | string | '.ln-apply' | Selector of the "Apply" button
- | allSwatches | string | '.swatch-option-link-layered' | Selector of the swatch links
- | selectedSwatches | string | '.swatch-option-link-layered.selected' | Selector of the selected swatch links
- | allLinks | string | '.filter-link' | Selector of the filter links
- | selectedLinks | string | '.filter-link.selected' | Selector of the selected filter links
- | filterContent | string | '.filter-options-content' | Selector of the filter content
- | blockFilters | string | '.block.filter' | Selector of the filter block
enableFilterRemember || boolean | false | Automatically apply the previously selected filter options
filterRememberDataAttr || string | '[data-role="filter-remember"]' | Selector of the element containing your items
enableMoreLess || boolean | true | Toggle filter items visibility by clicking on the show more/less action
moreLessAction || string | '[data-role="more-less-action"]' | Selector of the show more/less link (action element)
moreLessLists || string | '[data-role="more-less-block"]' | Selector of the hidden list (block) of the filter items
viewCustom || string | '' | Extends core View. Example: 'Digidirect_LayeredNavigation/js/dist/views/catalog/index'
addOn || string | '' | Add-ons communicate between component and the global store

## Example of Settings
```javascript
    triggerApplyButton: false,
    triggerApplyMode: false,
    applyModeBreakpoint: '(max-width: 767px)',
    enableAjax: false,
    enabledSeoUrls: false,
    postfix: '',
    baseUrl: '',
    selectors: {
        applyButton: '.ln-apply',
        allSwatches: '.swatch-option-link-layered',
        selectedSwatches: '.swatch-option-link-layered.selected',
        allLinks: '.filter-link',
        selectedLinks: '.filter-link.selected',
        filterContent: '.filter-options-content',
        blockFilters: '.block.filter'
    },
    enableFilterRemember: false,
    filterRememberDataAttr: '[data-role="filter-remember"]',
    viewCustom: '',
    addOn: ''
```

## Show More/Less Settings
Option | Type | Default | Description
------ | ---- | ------- | -----------
enableMoreLess | boolean | true | Toggle filter items visibility by clicking on the show more/less action
filterContent | string | '.filter-options-content' | Selector of the filter content
moreLessLists | string | '[data-role="more-less-block"]' | Selector of the hidden list (block) of the filter items

## Example of Settings
```javascript
    enableMoreLess: true,
    filterContent: '.filter-options-content',
    moreLessLists: '[data-role="more-less-block"]',
```