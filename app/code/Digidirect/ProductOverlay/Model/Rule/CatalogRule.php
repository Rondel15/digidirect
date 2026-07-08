<?php
namespace Digidirect\ProductOverlay\Model\Rule;

use Digidirect\ProductOverlay\Model\Overlays;
use Magento\Framework\Model\AbstractModel;
use Digidirect\ProductOverlay\Model\ResourceModel\Rule\CatalogRule as ResourceCatalogRule;

/**
 * Class CatalogRule
 *
 * @package Digidirect\ProductOverlay\Model\Rule
 * @method \Digidirect\ProductOverlay\Model\ResourceModel\Rule\CatalogRule getResource()
 */
class CatalogRule extends AbstractModel implements ProcessorInterface
{
    /**
     * @var array
     */
    protected $productByRuleId = [];

    /**
     * Setup resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceCatalogRule::class);
        parent::_construct();
    }

    /**
     * @param Overlays $overlay
     * @return bool
     */
    public function isApplicable(Overlays $overlay)
    {
        /**
         * @var $product \Magento\Catalog\Model\Product
         */
        $product = $overlay->getProduct();
        if (!$product) {
            return false;
        }

        if (!$overlay->getId()) {
            return false;
        }

        $ruleIds = $overlay->getCatalogPriceRulesIds();
        if (empty($ruleIds)) {
            return false;
        }

        if (empty($this->productByRuleId[$product->getId()])) {
            $this->productByRuleId = $this->getResource()->getPriceRuleProductsByRule($ruleIds);
        }

        foreach ($ruleIds as $item) {
            if (isset($this->productByRuleId[$product->getId()])
                && in_array($item, $this->productByRuleId[$product->getId()])
            ) {
                return true;
            }
        }

        return false;
    }
}
