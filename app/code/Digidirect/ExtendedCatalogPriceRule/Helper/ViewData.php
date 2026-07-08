<?php
namespace Digidirect\ExtendedCatalogPriceRule\Helper;

use Digidirect\ExtendedCatalogPriceRule\Api\Data\RuleDisplayMessageInterface;
use Digidirect\ExtendedCatalogPriceRule\Api\ExtendedCatalogRuleManagementInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Psr\Log\LoggerInterface;

/**
 * Class ViewData
 * @package Digidirect\ExtendedCatalogPriceRule\Helper
 */
class ViewData
{
    /**
     * @var ExtendedCatalogRuleManagementInterface
     */
    protected $extendedCatalogRuleManagement;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ViewData constructor.
     * @param ExtendedCatalogRuleManagementInterface $extendedCatalogRuleManagement
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     */
    public function __construct(
        ExtendedCatalogRuleManagementInterface $extendedCatalogRuleManagement,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        DateTime $dateTime,
        LoggerInterface $logger
    ) {
        $this->extendedCatalogRuleManagement = $extendedCatalogRuleManagement;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
    }

    /**
     * @param array $productIds
     * @return array|bool
     */
    public function getExtendedRulesDataForView(array $productIds)
    {
        try {
            $websiteId = $this->getWebsiteId();
            $customerGroupId = $this->getCustomerGroupId();
            $now = $this->getCurrentTimeStamp();

            $ruleData = $this->extendedCatalogRuleManagement->getExtendedRulesGroupedByProductIds(
                $productIds,
                RuleDisplayMessageInterface::ACTION_CODE,
                $websiteId,
                $customerGroupId,
                $now,
                true
            );
        } catch (\Exception $e) {
            $this->logger->error(
                __('\Can\'t get Extended Catalog Rules for product list. Error: %1', $e->getMessage())
            );
            return false;
        }
        return $ruleData;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->customerSession->getCustomerGroupId();
    }

    /**
     * @return int
     */
    public function getCurrentTimeStamp()
    {
        return $this->dateTime->gmtTimestamp();
    }
}
