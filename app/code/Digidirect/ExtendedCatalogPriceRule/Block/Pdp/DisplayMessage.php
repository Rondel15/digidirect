<?php
namespace Digidirect\ExtendedCatalogPriceRule\Block\Pdp;

use Digidirect\ExtendedCatalogPriceRule\Api\Data\RuleDisplayMessageInterface;
use Digidirect\ExtendedCatalogPriceRule\Api\ExtendedCatalogRuleManagementInterface;
use Digidirect\ExtendedCatalogPriceRule\Block\ExtendedRule;
use Digidirect\ExtendedCatalogPriceRule\Helper\Data;
use Digidirect\ExtendedCatalogPriceRule\Helper\ViewData;
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;

/**
 * Class DisplayMessage
 * @package Digidirect\ExtendedCatalogPriceRule\Block
 */
class DisplayMessage extends ExtendedRule
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var ExtendedCatalogRuleManagementInterface
     */
    protected $extendedCatalogRuleManagement;

    /**
     * @var ViewData
     */
    protected $viewData;

    /**
     * DisplayMessage constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Data $helper
     * @param ViewData $viewData
     * @param Registry $registry
     * @param ExtendedCatalogRuleManagementInterface $extendedCatalogRuleManagement
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Data $helper,
        ViewData $viewData,
        Registry $registry,
        ExtendedCatalogRuleManagementInterface $extendedCatalogRuleManagement,
        array $data = []
    ) {
        parent::__construct($context, $helper, $data);
        $this->coreRegistry = $registry;
        $this->extendedCatalogRuleManagement = $extendedCatalogRuleManagement;
        $this->viewData = $viewData;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * @return array|bool
     */
    public function getDisplayMessageRules()
    {
        $product = $this->getProduct();
        if (!$product) {
            return false;
        }
        try {
            $websiteId = $this->viewData->getWebsiteId();
            $customerGroupId = $this->viewData->getCustomerGroupId();
            $now = $this->viewData->getCurrentTimeStamp();

            return $this->extendedCatalogRuleManagement->getExtendedRulesDataByProductIds(
                [$product->getId()],
                RuleDisplayMessageInterface::ACTION_CODE,
                $websiteId,
                $customerGroupId,
                $now
            );
        } catch (\Exception $exception) {
            $this->_logger->error(__('Can\'t display Extended Catalog Rule. Error: %1', $exception->getMessage()));
            return false;
        }
    }
}
