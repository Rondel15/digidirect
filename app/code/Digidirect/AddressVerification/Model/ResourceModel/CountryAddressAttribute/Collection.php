<?php
namespace Digidirect\AddressVerification\Model\ResourceModel\CountryAddressAttribute;

use Digidirect\AddressVerification\Model\CountryAddress\Source\Attribute;
use Digidirect\AddressVerification\Model\ResourceModel\CountryAddressAttribute;
use Magento\Store\Model\Store;

/**
 * Class Collection
 * @package Digidirect\AddressVerification\Model\ResourceModel\Location
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $idFieldName = 'entity_id';

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * Locale model
     *
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $localeLists;

    /**
     * @var Attribute
     */
    protected $attributeSource;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param Attribute $attributeSource
     * @param null $connection
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        Attribute $attributeSource,
        $connection = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection);
        $this->jsonDecoder = $jsonDecoder;
        $this->localeLists = $localeLists;
        $this->attributeSource = $attributeSource;
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(
            'Digidirect\AddressVerification\Model\CountryAddressAttribute',
            'Digidirect\AddressVerification\Model\ResourceModel\CountryAddressAttribute'
        );
    }

    /**
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->prepareAttributes();
        return parent::_afterLoad();
    }

    /**
     * @return $this
     */
    protected function prepareAttributes()
    {
        foreach ($this as $item) {
            $attributes = $this->jsonDecoder->decode($item->getAttributes());
            $item->setAttributes($attributes);
            $item->setData('attribute_titles', implode(', ', $this->attributeSource->getNameByCode($attributes)));
            $item->setData(
                'country_name',
                (string)$this->localeLists->getCountryTranslation($item->getCountryCode())
            );
        }
        return $this;
    }
}
