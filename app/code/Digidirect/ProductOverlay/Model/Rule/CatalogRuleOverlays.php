<?php
namespace Digidirect\ProductOverlay\Model\Rule;

use Digidirect\ProductOverlay\Model\Overlays;
use Magento\CatalogRule\Api\Data\RuleInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Staging\Api\UpdateRepositoryInterface;
use Magento\Staging\Model\ResourceModel\Db\ReadEntityVersion;

class CatalogRuleOverlays implements ProcessorInterface
{
    const OVERLAY_ENTITY_TYPE = 'overlay';
    const OVERLAY_IDS = 'overlay_ids';

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var \Digidirect\ProductOverlay\Model\ResourceModel\Rule\CatalogRule
     */
    protected $resource;

    /**
     * @var array
     */
    protected $productByRuleId = [];

    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Rule\Collection
     */
    protected $activeRulesCache;

    /**
     * @var UpdateRepositoryInterface
     */
    protected $updateRepository;

    /**
     * @var ReadEntityVersion
     */
    protected $readEntityVersion;

    /**
     * CatalogRuleOverlays constructor.
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     * @param \Digidirect\ProductOverlay\Model\ResourceModel\Rule\CatalogRule $resource
     * @param UpdateRepositoryInterface $updateRepository
     * @param ReadEntityVersion $readEntityVersion
     */
    public function __construct(
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Digidirect\ProductOverlay\Model\ResourceModel\Rule\CatalogRule $resource,
        UpdateRepositoryInterface $updateRepository = null,
        ReadEntityVersion $readEntityVersion = null
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->resource = $resource;
        $this->updateRepository = $updateRepository ?: ObjectManager::getInstance()->get(
            UpdateRepositoryInterface::class
        );
        $this->readEntityVersion = $readEntityVersion ?: ObjectManager::getInstance()->get(
            ReadEntityVersion::class
        );
    }

    /**
     * @param Overlays $overlay
     * @return bool|null
     */
    public function isApplicable(Overlays $overlay)
    {
        /**
         * @var $product \Magento\Catalog\Model\Product
         */
        $product = $overlay->getProduct();
        if (!$product || !$overlay->getId()) {
            return false;
        }

        /** Ignore checks if an overlay is not assigned to catalog rules or staging updates */
        if (!$this->isCatalogRuleOverlay($overlay->getId())) {
            return null;
        }

        $ruleIds = $this->getCatalogPriceRulesIds($overlay->getId());
        if (empty($ruleIds)) {
            return false;
        }

        $this->productByRuleId = $this->resource->getPriceRuleProductsByRule($ruleIds);

        foreach ($ruleIds as $item) {
            if (isset($this->productByRuleId[$product->getId()])
                && in_array($item, $this->productByRuleId[$product->getId()])
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $overlayId
     * @return array
     */
    protected function getCatalogPriceRulesIds($overlayId)
    {
        return $this->getActiveRuleIds(
            $this->storeManager->getStore()->getWebsiteId(),
            $this->customerSession->getCustomerGroupId(),
            $overlayId
        );
    }

    /**
     * @param int $websiteId
     * @param int $customerGroupId
     * @param int $overlayId
     * @return array
     */
    protected function getActiveRuleIds($websiteId, $customerGroupId, $overlayId)
    {
        if (null === $this->activeRulesCache) {
            $activeRules = $this->ruleCollectionFactory->create()
                ->addFieldToSelect('rule_id')
                ->addWebsiteFilter($websiteId)
                ->addCustomerGroupFilter($customerGroupId)
                ->addFieldToFilter('is_active', 1);

            $activeRules->getSelect()->join(
                ['overlay' => $activeRules->getTable('digidirect_product_overlay_catalogrule')],
                'main_table.row_id = overlay.row_id',
                'overlay_id'
            );

            $this->activeRulesCache = $activeRules->getData();
        }

        $ruleIds = [];
        foreach ($this->activeRulesCache as $rule) {
            if ($rule['overlay_id'] == $overlayId) {
                $ruleIds[] = $rule['rule_id'];
            }
        }

        return $ruleIds;
    }

    /**
     * Check if an overlay is assigned to catalog rules or staging updates
     *
     * @param int $overlayId
     * @return int
     */
    protected function isCatalogRuleOverlay($overlayId)
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($connection->getTableName('digidirect_product_overlay_catalogrule'), 'COUNT(*)')
            ->where('overlay_id = ?', $overlayId);

        return (int)$connection->fetchOne($select);
    }

    /**
     * @param int $overlayId
     * @param bool $disableStagingPreview
     * @return array
     */
    public function getCatalogRulesByOverlayId($overlayId, $disableStagingPreview = false)
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(['rule' => $connection->getTableName('catalogrule')])
            ->join(
                ['overlay' => $connection->getTableName('digidirect_product_overlay_catalogrule')],
                'rule.row_id = overlay.row_id'
            )
            ->where('overlay.overlay_id = ?', $overlayId);

        if ($disableStagingPreview) {
            $select->setPart('disable_staging_preview', true);
        }

        $rules = $connection->fetchAll($select);
        foreach ($rules as &$rule) {
            /** @var \Magento\CatalogRule\Api\Data\RuleInterface $rule */
            $rule['start_time'] = $rule['end_time'] = null;

            try {
                $update = $this->updateRepository->get($rule['created_in']);
            } catch (LocalizedException $e) {
                continue;
            }

            $rule['start_time'] = $update->getStartTime();
            $nextUpdate = $this->getNextScheduleUpdate($update->getId(), $rule['rule_id']);
            if ($nextUpdate) {
                $rule['end_time'] = $nextUpdate->getStartTime();
            }
        }

        return $rules;
    }

    /**
     * @param int $updateId
     * @param int $entityId
     * @return \Magento\Staging\Api\Data\UpdateInterface|false
     */
    protected function getNextScheduleUpdate($updateId, $entityId)
    {
        $nextVersionId = $this->readEntityVersion->getNextVersionId(
            RuleInterface::class,
            $updateId,
            $entityId
        );

        try {
            return $this->updateRepository->get($nextVersionId);
        } catch (LocalizedException $e) {
            return false;
        }
    }

    /**
     * @param int $ruleId
     * @param bool $disableStagingPreview
     * @return array
     * @throws \Zend_Db_Select_Exception
     */
    public function getOverlayIdsByCatalogRuleId($ruleId, $disableStagingPreview = false)
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(
                ['rule' => $connection->getTableName('catalogrule')],
                []
            )
            ->join(
                ['overlay' => $connection->getTableName('digidirect_product_overlay_catalogrule')],
                'rule.row_id = overlay.row_id',
                ['overlay_id']
            )
            ->where('rule.rule_id = ?', $ruleId);

        if ($disableStagingPreview) {
            $select->setPart('disable_staging_preview', true);
        }

        return $connection->fetchCol($select);
    }

    /**
     * @param int $overlayId
     * @param int $ruleRowId
     * @return int
     * @throws CouldNotDeleteException
     */
    public function deleteRelation($overlayId, $ruleRowId)
    {
        $connection = $this->resource->getConnection();
        $affectedRows = $connection->delete(
            $connection->getTableName('digidirect_product_overlay_catalogrule'),
            [
                'overlay_id = ?' => $overlayId,
                'row_id = ?' => $ruleRowId,
            ]
        );

        if (!$affectedRows) {
            throw new CouldNotDeleteException(__('Can\'t find an item to delete.'));
        }

        return $affectedRows;
    }
}
