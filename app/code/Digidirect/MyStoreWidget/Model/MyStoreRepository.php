<?php
namespace Digidirect\MyStoreWidget\Model;

use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\MyStoreWidget\Api\MyStoreRepositoryInterface;
use Digidirect\MyStoreWidget\Api\Data\MyStoreInterface;
use Digidirect\MyStoreWidget\Helper\Config as Helper;
use Digidirect\MyStoreWidget\Model\ResourceModel\MyStore;
use Digidirect\MyStoreWidget\Model\ResourceModel\MyStore\CollectionFactory;
use Digidirect\MyStoreWidget\Model\ResourceModel\MyStore\Collection as MyStoreCollection;
use \Digidirect\MyStoreWidget\Model\ResourceModel\MyStoreIndex;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity\Collection as AbstractEntityCollection;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity\CollectionFactory as AbstractEntityCollectionFactory;
use Magento\Customer\Model\Customer;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\DB\Helper as DbHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;

/**
 * Class MyStoreRepository
 *
 * @package Digidirect\MyStoreWidget\Model
 */
class MyStoreRepository implements MyStoreRepositoryInterface
{
    /**
     * @var MyStoreFactory
     */
    protected $myStoreFactory;

    /**
     * @var AbstractEntityCollectionFactory
     */
    protected $abstractEntityCollectionFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var DbHelper
     */
    protected $dbHelper;

    /**
     * @var MyStoreIndex
     */
    protected $myStoreIndex;

    /**
     * @var CollectionFactory
     */
    protected $myStoreCollectionFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * MyStoreRepository constructor.
     *
     * @param \Digidirect\MyStoreWidget\Model\MyStoreFactory $myStoreFactory
     * @param CollectionFactory $myStoreCollectionFactory
     * @param AbstractEntityCollectionFactory $abstractEntityCollectionFactory
     * @param MyStoreIndex $myStoreIndex
     * @param Helper $helper
     * @param DbHelper $dbHelper
     * @param Registry $registry
     */
    public function __construct(
        MyStoreFactory $myStoreFactory,
        CollectionFactory $myStoreCollectionFactory,
        AbstractEntityCollectionFactory $abstractEntityCollectionFactory,
        MyStoreIndex $myStoreIndex,
        Helper $helper,
        DbHelper $dbHelper,
        Registry $registry
    ) {
        $this->myStoreFactory = $myStoreFactory;
        $this->myStoreIndex = $myStoreIndex;
        $this->abstractEntityCollectionFactory = $abstractEntityCollectionFactory;
        $this->helper = $helper;
        $this->dbHelper = $dbHelper;
        $this->myStoreCollectionFactory = $myStoreCollectionFactory;
        $this->registry = $registry;
    }

    /**
     * @param MyStoreInterface $myStoreEntity
     * @return MyStoreInterface
     * @throws CouldNotSaveException
     */
    public function save(MyStoreInterface $myStoreEntity)
    {
        try {
            $myStoreEntity->getResource()->save($myStoreEntity);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the store: %1',
                $exception->getMessage()
            ));
        }

        return $myStoreEntity;
    }

    /**
     * @param int $customerId
     * @param string $type
     * @return MyStoreInterface
     */
    public function getByCustomerId($customerId, $type = MyStoreInterface::DEFAULT_TYPE)
    {
        /** @var MyStoreCollection $collection */
        $collection = $this->myStoreCollectionFactory->create();
        $collection->addFieldToFilter(MyStoreInterface::CUSTOMER_ID, $customerId);
        $collection->addFieldToFilter('type', $type);

        /** @var MyStoreInterface $myStoreEntity */
        $myStoreEntity = $collection->getFirstItem();
        if (!$myStoreEntity->getCustomerId()) {
            $myStoreEntity->setCustomerId($customerId);
            $myStoreEntity->setType($type);
        }
        return $myStoreEntity;
    }

    /**
     * @param int $customerId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteByCustomerId($customerId)
    {
        $myStoreEntity = $this->getByCustomerId($customerId);
        try {
            $myStoreEntity->getResource()->delete($myStoreEntity);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the store: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param null $searchTerm
     * @return AbstractEntityCollection|array
     * @throws InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoresCollection($searchTerm = null)
    {
        if ($searchTerm !== null && strlen($searchTerm) < $this->helper->getSearchMinLength()) {
            throw new InputException(new Phrase(
                'Search query max length is "%max" symbols',
                ['max' => $this->helper->getSearchMinLength()]
            ));
        }

        $attributes = $this->helper->getAttributes();
        return $this->myStoreIndex->searchStores($searchTerm, $attributes);
    }

    /**
     * @param DataObject $address
     * @return bool|AbstractEntityInterface
     */
    public function getStoreByAddress(DataObject $address)
    {
        $fieldsInfo = $this->helper->getShippingAddressFieldsInfo();
        if (!empty($fieldsInfo)) {
            $responseAttributes = $this->helper->getResponseAttributes();
            /** @var AbstractEntityCollection $entityCollection */
            $entityCollection = $this->abstractEntityCollectionFactory->create()
                ->addAttributeToSelect($responseAttributes, true)
                ->addFieldToFilter(AbstractEntityInterface::STATUS, 1)
                ->addFieldToFilter(AbstractEntityInterface::ATTRIBUTE_SET_ID, [
                    'in' => $this->helper->getEntities(),
                ]);

            if ($entityId = (int)$address->getAbstractEntityId()) {
                $entityCollection->addFieldToFilter(
                    AbstractEntityInterface::ENTITY_ID,
                    $entityId
                );
            } else {
                foreach ($fieldsInfo as $fieldInfo) {
                    $entityCollection->addFieldToFilter(
                        $fieldInfo['entity_attribute'],
                        $address->getData($fieldInfo['shipping_field'])
                    );
                }
            }

            $entityCollection->setPage(1, 1);
            /** @var \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface $item */
            $item = $entityCollection->getFirstItem();
            if ($item->getId()) {
                $item->loadRelatedAttributes($responseAttributes);
                return $item;
            }
        }
        return false;
    }

    /**
     * @param Customer $customer
     * @param DataObject $address
     * @return bool
     */
    public function setMyStoreByShippingAddress(Customer $customer, DataObject $address = null)
    {
        if ($address === null) {
            $address = $customer->getDefaultShippingAddress();
        }

        if ($address) {
            $store = $this->getStoreByAddress($address);
            if (!$store) {
                return false;
            }

            $myStore = $this->getByCustomerId($customer->getId());
            $myStore->setAbstractEntityId($store->getId());
            $this->save($myStore);
        }
        return true;
    }

    /**
     * @param integer $customerId
     * @param string $searchText
     * @return AbstractEntityCollection
     * @throws LocalizedException
     */
    public function saveForTypeTextInput($customerId, $searchText)
    {
        if(empty($searchText))
        {
            throw new LocalizedException(__('Your Store is not changed.'));
        }
        if (!trim($searchText)) {
            throw new LocalizedException(__('Your Store is not changed.'));
        }
        $stores = $this->getStoresCollection($searchText);
        if (!$stores) {
            throw new LocalizedException(__('Your Store is not changed.'));
        }
        foreach ($stores as $store) {
            $storeId = $store[AbstractEntityInterface::ENTITY_ID];
            $type = $store['type'];
            $registryKey = 'abstract_entity_id_' . $type;
            $this->registry->unregister($registryKey);
            $this->registry->register($registryKey, $storeId);

            if ($customerId) {
                $store = $this->getByCustomerId($customerId, $type);
                if ($store) {
                    $store->setAbstractEntityId($storeId);
                    $store->setSearchText($searchText);
                    $store->setType($type);
                    $this->save($store);
                }
            }
        }

        return $stores;
    }

    /**
     * @param integer $myStoreId
     * @param integer $customerId
     * @return integer
     * @throws LocalizedException
     */
    public function saveForTypeAutocomplete($myStoreId, $customerId)
    {
        if (!$myStoreId) {
            throw new LocalizedException(__('Your Store is not changed.'));
        }

        $this->registry->unregister('abstract_entity_id');
        $this->registry->register('abstract_entity_id', $myStoreId);

        if ($customerId) {
            $model = $this->getByCustomerId($customerId);
            $model->setCustomerId($customerId);
            $model->setAbstractEntityId($myStoreId);
            $this->save($model);
        }

        return $myStoreId;
    }
}
