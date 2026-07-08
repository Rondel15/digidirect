<?php

namespace Digidirect\CheckoutFields\Model;

use Digidirect\CheckoutFields\Api\Data\QuoteFieldValueExtensionInterface;
use Digidirect\CheckoutFields\Api\Data\QuoteFieldValueInterface;
use Digidirect\CheckoutFields\Api\QuoteFieldValueRepositoryInterface;
use Digidirect\CheckoutFields\Helper\Xml\Fields\Parser;
use Digidirect\CheckoutFields\Model\ResourceModel\QuoteFieldValue\CollectionFactory as QuoteFieldValueCollectionFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote;

/**
 * Class QuoteFieldValue
 *
 * @package Digidirect\CheckoutFields\Model
 */
class QuoteFieldValue extends AbstractExtensibleModel implements IdentityInterface, QuoteFieldValueInterface
{
    const CACHE_TAG = 'digidirect_checkout_fields';

    /**
     * @var string
     */
    protected $_eventPrefix = 'digidirect_checkoutfields_quote';

    /**
     * @var QuoteFieldValueRepositoryInterface
     */
    protected $quoteFieldValueRepository;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var QuoteFieldValueCollectionFactory
     */
    protected $valueCollectionFactory;

    /**
     * QuoteFieldValue constructor.
     *
     * @param Context                                 $context
     * @param Registry                                $registry
     * @param Parser                                  $parser
     * @param AbstractResource|null                   $resource
     * @param AbstractDb|null                         $resourceCollection
     * @param array                                   $data
     * @param ExtensionAttributesFactory|null         $extensionFactory
     * @param AttributeValueFactory|null              $customAttributeFactory
     * @param QuoteFieldValueRepositoryInterface|null $quoteFieldValueRepository
     * @param QuoteFieldValueCollectionFactory|null   $quoteFieldValueCollectionFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Parser $parser,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null,
        QuoteFieldValueRepositoryInterface $quoteFieldValueRepository = null,
        QuoteFieldValueCollectionFactory $quoteFieldValueCollectionFactory = null
    ) {
        $this->parser = $parser;
        $this->quoteFieldValueRepository = $quoteFieldValueRepository ?? ObjectManager::getInstance()->get(
                QuoteFieldValueRepositoryInterface::class
            );
        $extensionFactory = $extensionFactory ?? ObjectManager::getInstance()->get(
            ExtensionAttributesFactory::class
            );

        $customAttributeFactory = $customAttributeFactory ?? ObjectManager::getInstance()->get(
                AttributeValueFactory::class
            );

        $this->valueCollectionFactory = $quoteFieldValueCollectionFactory
                                        ?? ObjectManager::getInstance()->get(QuoteFieldValueCollectionFactory::class);

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\QuoteFieldValue::class);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId()];
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @return int|null
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * @return string|null
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * @return string|null
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * @return string|null
     */
    public function getFieldId()
    {
        return $this->getData(self::FIELD_ID);
    }

    /**
     * @param string $code
     *
     * @return $this
     */
    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * @param int $quoteId
     *
     * @return $this
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * @param string|array $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * @param string $fieldId
     *
     * @return $this
     */
    public function setFieldId($fieldId)
    {
        return $this->setData(self::FIELD_ID, $fieldId);
    }

    /**
     * Delete quote values after saving to order
     *
     * @param int $quoteId
     * @return void
     * @deprecated 2.0
     */
    public function cleanDataBeforeSave($quoteId)
    {
        $items = $this->quoteFieldValueRepository->getListByQuoteId($quoteId);

        /**
         * @var $item $this
         */
        foreach ($items as $item) {
            $this->quoteFieldValueRepository->delete($item);
        }
    }

    /**
     * @param Quote $quote
     * @param array $params
     * @param bool $reSave
     * @return void
     * @throws LocalizedException
     * @deprecated 2.0
     */
    public function saveCustomFieldsValuesToQuote($quote, $params = [], $reSave = true)
    {
        $this->quoteFieldValueRepository->saveToQuote($quote, $params, $reSave);
    }

    /**
     * Get custom fields quote
     *
     * @param $quoteId
     *
     * @return ResourceModel\QuoteFieldValue\Collection
     */
    public function getCustomFields($quoteId)
    {
        $collection = $this->valueCollectionFactory->create();

        return $collection->addFieldToFilter(self::QUOTE_ID, ['eq' => $quoteId]);
    }

    /**
     * Get custom fields quote
     *
     * @param int $quoteId
     * @param string $fieldId
     *
     * @return ResourceModel\QuoteFieldValue\Collection
     */
    public function getCustomField($quoteId, $fieldId)
    {
        $collection = $this->valueCollectionFactory->create();

        return $collection
                    ->addFieldToFilter(self::QUOTE_ID, ['eq' => $quoteId])
                    ->addFieldToFilter(self::FIELD_ID, ['eq' => $fieldId]);
    }

    /**
     * {@inheritDoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritDoc}
     */
    public function setExtensionAttributes(QuoteFieldValueExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
