<?php
namespace Digidirect\MyStoreWidget\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Digidirect\MyStoreWidget\Api\Data\MyStoreInterface;

/**
 * Class MyStore
 * @package Digidirect\MyStoreWidget\Model
 */
class MyStore extends AbstractModel implements MyStoreInterface, IdentityInterface
{
    const CACHE_TAG = 'digidirect_mystore_widget';

    /**
     * Name prefix of events that are dispatched by model
     *
     * @var string
     */
    protected $_eventPrefix = 'digidirect_mystore_widget';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Digidirect\MyStoreWidget\Model\ResourceModel\MyStore');
        $this->setIdFieldName(MyStoreInterface::ID);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->getData(MyStoreInterface::ID);
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->getData(MyStoreInterface::CUSTOMER_ID);
    }

    /**
     * @return mixed
     */
    public function getAbstractEntityId()
    {
        return $this->getData(MyStoreInterface::ABSTRACT_ENTITY_ID);
    }

    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(MyStoreInterface::CUSTOMER_ID, $customerId);
    }

    /**
     * @param int $entityId
     * @return $this
     */
    public function setAbstractEntityId($entityId)
    {
        return $this->setData(MyStoreInterface::ABSTRACT_ENTITY_ID, $entityId);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(MyStoreInterface::ID, $id);
    }

    /**
     * @return string
     */
    public function getSearchText()
    {
        return $this->getData(MyStoreInterface::SEARCH_TEXT);
    }

    /**
     * @param string $text
     * @return mixed
     */
    public function setSearchText($text)
    {
        return $this->setData(self::SEARCH_TEXT, $text);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }
}
