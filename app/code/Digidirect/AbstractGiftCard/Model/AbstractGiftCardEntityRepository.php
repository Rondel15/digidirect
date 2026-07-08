<?php

namespace Digidirect\AbstractGiftCard\Model;

use Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface;
use Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Price model for external catalogs
 */
class AbstractGiftCardEntityRepository implements AbstractGiftCardEntityRepositoryInterface
{
    /**
     * @var \Digidirect\AbstractGiftCard\Model\ResourceModel\AbstractGiftCardEntity
     */
    protected $_resource;

    /**
     * @var AbstractGiftCardEntityFactory
     */
    protected $_factory;

    /**
     * AbstractGiftCardEntityRepository constructor.
     * @param ResourceModel\AbstractGiftCardEntity $resource
     * @param AbstractGiftCardEntityFactory $factory
     */
    public function __construct(
        \Digidirect\AbstractGiftCard\Model\ResourceModel\AbstractGiftCardEntity $resource,
        \Digidirect\AbstractGiftCard\Model\AbstractGiftCardEntityFactory $factory
    ) {
        $this->_resource = $resource;
        $this->_factory = $factory;
    }

    /**
     * @param int $id
     * @return AbstractGiftCardEntityInterface
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        $entity = $this->_factory->create();
        $this->_resource->load($entity, $id);
        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('Requested abstract giftcard doesn\'t exist'));
        }
        return $entity;
    }

    /**
     * @param AbstractGiftCardEntityInterface $entity
     * @return $this
     */
    public function save(AbstractGiftCardEntityInterface $entity)
    {
        $this->_resource->save($entity);
        return $this;
    }

    /**
     * @param string $serviceCode
     * @param string $giftCardCode
     * @return AbstractGiftCardEntityInterface
     * @throws NoSuchEntityException
     */
    public function getGiftCardDataByServiceAndCode($serviceCode, $giftCardCode)
    {
        $entity = $this->_factory->create();
        $this->_resource->loadByServiceAndCode($entity, $serviceCode, $giftCardCode);
        if ($entity->getId() === null) {
            throw new NoSuchEntityException(__('Requested abstract giftcard doesn\'t exist'));
        }
        return $entity;
    }

    /**
     * @param \Magento\GiftCardAccount\Model\Giftcardaccount $giftcardaccount
     * @return AbstractGiftCardEntityInterface
     * @throws NoSuchEntityException
     */
    public function loadByGiftCardAccount(\Magento\GiftCardAccount\Model\Giftcardaccount $giftcardaccount)
    {
        $entity = $this->_factory->create();
        $this->_resource->load($entity, $giftcardaccount->getId(), 'giftcard_account_id');
        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('Requested abstract giftcard doesn\'t exist'));
        }
        return $entity;
    }

    /**
     * @param \Magento\Framework\DataObject $entityOrderData
     * @return \Digidirect\AbstractGiftCard\Model\ResourceModel\AbstractGiftCardEntity
     */
    public function saveEntityOrderData(\Magento\Framework\DataObject $entityOrderData)
    {
        return $this->_resource->saveEntityOrderData($entityOrderData);
    }
}
