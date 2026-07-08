<?php
namespace Digidirect\AbstractAttributes\Observer\Adminhtml;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Store;

/**
 * Class CatalogEntityAttributeLoadAfter
 * @package Digidirect\AbstractAttributes\Observer\Adminhtml
 */
class CatalogEntityAttributeLoadAfter implements ObserverInterface
{
    /**
     * @var \Digidirect\AbstractAttributes\Api\AbstractAttributeRepositoryInterface
     */
    protected $_repository;

    /**
     * CatalogEntityAttributeLoadAfter constructor.
     * @param \Digidirect\AbstractAttributes\Api\AbstractAttributeRepositoryInterface $repository
     */
    public function __construct(
        \Digidirect\AbstractAttributes\Api\AbstractAttributeRepositoryInterface $repository
    ) {
        $this->_repository = $repository;
    }

    /**
     * Execute
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $attribute = $observer->getAttribute();
        if ($id = $attribute->getId()) {
            try {
                $abstractAttribute = $this->_repository->getByAttributeId($id, Store::DEFAULT_STORE_ID);
                $attribute->setData('aa_status', $abstractAttribute->getStatus());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            }
        }
        return $this;
    }
}
