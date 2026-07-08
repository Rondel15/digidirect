<?php
namespace Digidirect\Collect\Plugin\Model\Quote\Item;

use Magento\Quote\Model\Quote\Item\Compare as Subject;

class Compare
{
    /**
     * @param Subject $subject
     * @param array $result
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return mixed
     */
    public function afterGetOptions(Subject $subject, $result, \Magento\Quote\Model\Quote\Item $item)
    {
        unset($result['info_buyRequest']['collect_place_storage_name']);
        unset($result['info_buyRequest']['collect_quote_item_id']);
        unset($result['info_buyRequest']['cart_collect_place_id']);
        unset($result['info_buyRequest']['collect_product_id']);
        unset($result['info_buyRequest']['collect_simple_product_id']);
        unset($result['info_buyRequest']['collect_distance']);
        unset($result['info_buyRequest']['collect_place_id']);

        return $result;
    }
}
