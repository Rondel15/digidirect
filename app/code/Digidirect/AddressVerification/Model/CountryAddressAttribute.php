<?php
namespace Digidirect\AddressVerification\Model;

use Digidirect\AddressVerification\Api\Data\CountryAddressAttributeInterface;
use Digidirect\AddressVerification\Model\ResourceModel\CountryAddressAttribute\CollectionFactory;

/**
 * Class CountryAddressAttribute
 * @package Digidirect\AddressVerification\Model
 */
class CountryAddressAttribute extends \Magento\Framework\Model\AbstractModel implements CountryAddressAttributeInterface
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;
    
    /**
     * CountryAddressAttribute constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\CountryAddressAttribute $resource
     * @param CollectionFactory $resourceCollectionFactory
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Digidirect\AddressVerification\Model\ResourceModel\CountryAddressAttribute $resource,
        CollectionFactory $resourceCollectionFactory,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder
    ) {
        $resourceCollection = $resourceCollectionFactory->create();
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
        $this->jsonDecoder = $jsonDecoder;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * @return $this
     */
    public function encodeAttributes()
    {
        $attributes = $this->getAttributes();
        $attributes = !is_array($attributes) ? [$attributes] : $attributes;
        $this->setAttributes($this->jsonEncoder->encode($attributes));
        return $this->jsonEncoder->encode($attributes);
    }

    /**
     * @return []
     */
    public function getDecodedAttributes()
    {
        $attributes = '{}';
        if ($this->getAttributes()) {
            $attributes = $this->getAttributes();
        }
        return $this->jsonDecoder->decode($attributes);
    }
}
