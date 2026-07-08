<?php
namespace Digidirect\Utilities\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\SalesSequence\Model\ResourceModel\Meta as ResourceSequenceMeta;

/**
 * Class UtilitiesConfigSave
 *
 * @package Digidirect\Utilities\Observer
 */
class UtilitiesConfigSave implements ObserverInterface
{
    /**
     * ScopeConfigInterface
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Store Manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Resource Sequence Meta
     *
     * @var ResourceSequenceMeta
     */
    protected $resourceSequenceMeta;

    /**
     * UtilitiesConfigSave constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ResourceSequenceMeta $resourceSequenceMeta
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ResourceSequenceMeta $resourceSequenceMeta
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->resourceSequenceMeta = $resourceSequenceMeta;
    }

    /**
     * Execute
     *
     * @param EventObserver $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function execute(EventObserver $observer)
    {
        $this->updateSequenceProfile(0, 0);
        foreach ($this->storeManager->getStores() as $store) {
            $this->updateSequenceProfile($store->getId(), $store->getWebsiteId());
        }
        return true;
    }

    /**
     * Update Sequence Profile
     *
     * @param int $storeId
     * @param int $websiteId
     * @return void
     */
    protected function updateSequenceProfile($storeId, $websiteId)
    {
        $useWebsiteIdAsPrefix = $this->useStoreIdAsPrefix($storeId);
        foreach ($this->getEntityTypes() as $entityType) {
            $sequenceMeta = $this->resourceSequenceMeta->loadByEntityTypeAndStore(
                $entityType,
                $storeId
            );

            $prefix = '';
            if ($this->isActive($storeId)) {
                $prefix = $useWebsiteIdAsPrefix ? $websiteId : $this->getPrefix($entityType, $storeId);
            }

            if ($activeProfile = $sequenceMeta->getActiveProfile()) {
                $activeProfile->setPrefix($prefix)->save();
            }
        }
    }

    /**
     * Get Entity Types
     *
     * @return []
     */
    protected function getEntityTypes()
    {
        return ['order', 'invoice', 'shipment', 'creditmemo', 'rma_item'];
    }

    /**
     * Is Active
     *
     * @param string $scopeCode
     * @return bool
     */
    protected function isActive($scopeCode)
    {
        return (bool)$this->scopeConfig->getValue(
            'digidirect_utilities_config/sales_prefix/active',
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Use Store Id As Prefix
     *
     * @param string $scopeCode
     * @return bool
     */
    protected function useStoreIdAsPrefix($scopeCode)
    {
        return (bool)$this->scopeConfig->getValue(
            'digidirect_utilities_config/sales_prefix/use_store_id_as_prefix',
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Get Prefix
     *
     * @param string $entityType
     * @param string $scopeCode
     * @return string
     */
    protected function getPrefix($entityType, $scopeCode)
    {
        return $this->scopeConfig->getValue(
            "digidirect_utilities_config/sales_prefix/{$entityType}",
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }
}
