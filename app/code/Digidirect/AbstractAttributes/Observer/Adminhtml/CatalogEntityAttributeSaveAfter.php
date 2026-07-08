<?php
namespace Digidirect\AbstractAttributes\Observer\Adminhtml;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Store;

/**
 * Class CatalogEntityAttributeSaveAfter
 * @package Digidirect\AbstractAttributes\Observer\Adminhtml
 */
class CatalogEntityAttributeSaveAfter implements ObserverInterface
{
    /**
     * @var \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterfaceFactory
     */
    protected $_abstractAttributeFactory;

    /**
     * @var \Digidirect\AbstractAttributes\Api\AbstractAttributeRepositoryInterface
     */
    protected $_repository;

    /**
     * CatalogEntityAttributeSaveAfter constructor.
     * @param \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterfaceFactory $abstractAttributeFactory
     * @param \Digidirect\AbstractAttributes\Api\AbstractAttributeRepositoryInterface $repository
     */
    public function __construct(
        \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterfaceFactory $abstractAttributeFactory,
        \Digidirect\AbstractAttributes\Api\AbstractAttributeRepositoryInterface $repository
    ) {
        $this->_abstractAttributeFactory = $abstractAttributeFactory;
        $this->_repository = $repository;
    }

    /**
     * Execute
     * @param EventObserver $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        $attribute = $observer->getAttribute();
        $advOptions = $attribute->getData('aa');
        if (!$advOptions) {
            $advOptions = [];
        }
        $storeId = isset($advOptions['store_id']) ? $advOptions['store_id'] : Store::DEFAULT_STORE_ID;

        /**
         * If attribute was saved from option repository then skip save abstract attribute
         * @see \Digidirect\AbstractAttributes\Model\OptionRepository::save
         */
        if ($attribute->hasData('aa_status') && !$attribute->getSkipAaSave()) {
            $attrId = $attribute->getId();
            $status = $attribute->getData('aa_status');

            try {
                /** @var \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface $abstractAttribute */
                $abstractAttribute = $this->_repository->getByAttributeId($attrId, $storeId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                if (!$status) {
                    return $this;
                }

                $abstractAttribute = $this->_abstractAttributeFactory->create();
                $abstractAttribute->setAttributeId($attrId);
            }

            $abstractAttribute->setStatus($status);
            $abstractAttribute->addData($advOptions);

            if (!$abstractAttribute->getUrlKey()) {
                $abstractAttribute->setUrlKey($attribute->getDefaultFrontendLabel());
            }

            $this->_repository->save($abstractAttribute);
        }

        return $this;
    }
}
