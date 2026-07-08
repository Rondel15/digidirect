Digidirect Free Gift Bulk Popup Rules Widget
=====================

[wiki link](https://stash.digidirect.com/projects/LEGO/repos/digidirect_freegift/browse/view/frontend/web/js/free-gift-by-rules/rules.js)

### VERSION 1.0.0

The widget allows:
- Enable/disable free gifts (depending on the rules)
- Show the total counter of available gifts
- Show the rule counter of available gifts

## Settings

Rule counter:
Option | Type | Default | Description
------ | ---- | ------- | -----------
counter.wrapper | jQuery object | {} | Rule counter wrapper element
counter.count_wrapper | jQuery object | {} | Rule counter count wrapper element
counter.rule_wrapper | jQuery object | {} | Rule wrapper element
counter.count | integer | 0 | Counter buffer of available checked gifts (by rule)

Selectors:
Option | Type | Default | Description
------ | ---- | ------- | -----------
selectors.counter_count_wrapper | string | [data-free-gift-by-rules="count"] | Rule counter count
selectors.count_counter_rule_wrapper | string | [data-free-gift-by-rules="rule-wrapper"] | Rule wrapper
selectors.checkboxes | string | [data-free-gift-by-rules="checkbox"] | Rule checkboxes
selectors.uncheckedItems | string | [data-free-gift-by-rules-checked="off"] | Unchecked gifts
selectors.checkedItems | string | [data-free-gift-by-rules-checked="on"] | Checked gifts
selectors.total_count | string | [data-free-gift-by-rules="total-count"] | Total count of available checked gifts

Other options:
Option | Type | Default | Description
------ | ---- | ------- | -----------
formId | string | freegift_items_form | Form Id
ruleId | integer | 0 | Rule Id
ruleDiscountQty | integer | 0 | Available quantity of gifts (by rule)
disableItemClass | string | _disabled | Class for disabled gifts
submitButton | jQuery object | $('[data-free-gift-by-rules="submit"]') | Add to cart button