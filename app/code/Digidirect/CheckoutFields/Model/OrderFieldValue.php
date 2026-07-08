<?php

namespace Digidirect\CheckoutFields\Model;

use Digidirect\CheckoutFields\Api\Data\OrderFieldValueExtensionInterface;
use Digidirect\CheckoutFields\Api\Data\OrderFieldValueInterface;
use Digidirect\CheckoutFields\Api\OrderFieldValueRepositoryInterface;
use Digidirect\CheckoutFields\Api\QuoteFieldValueRepositoryInterface;
use Digidirect\CheckoutFields\Model\ResourceModel\OrderFieldValue\CollectionFactory as OrderValueCollectionFactory;
use Digidirect\CheckoutFields\Model\ResourceModel\QuoteFieldValue\CollectionFactory;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

/**
 * Class OrderFieldValue
 *
 * @package Digidirect\CheckoutFields\Model
 */
class OrderFieldValue extends AbstractExtensibleModel implements IdentityInterface, OrderFieldValueInterface
{
    const CACHE_TAG = 'digidirect_checkout_fields';

    /**
     * @var string
     */
    protected $_eventPrefix = 'digidirect_checkoutfields_order';

    /**
     * @var ResourceModel\QuoteFieldValue\CollectionFactory
     */
    protected $quoteFieldsCollectionFactory;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var OrderFieldValueRepositoryInterface
     */
    protected $orderFieldValueRepository;

    /**
     * @var QuoteFieldValueRepositoryInterface
     */
    protected $quoteFieldValueRepository;

    /**
     * @var OrderValueCollectionFactory
     */
    protected $orderValueCollectionFactory;

    /**
     * OrderFieldValue constructor.
     *
     * @param Context                                         $context
     * @param Registry                                        $registry
     * @param Session                                         $checkoutSession
     * @param ResourceModel\QuoteFieldValue\CollectionFactory $quoteFieldsCollectionFactory
     * @param AbstractResource|null                           $resource
     * @param AbstractDb                                      $resourceCollection
     * @param array                                           $data
     * @param ExtensionAttributesFactory                      $extensionFactory
     * @param AttributeValueFactory                           $customAttributeFactory
     * @param OrderFieldValueRepositoryInterface              $orderFieldValueRepository
     * @param QuoteFieldValueRepositoryInterface              $quoteFieldValueRepository
     * @param OrderValueCollectionFactory|null                $orderValueCollectionFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Session $checkoutSession,
        CollectionFactory $quoteFieldsCollectionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null,
        OrderFieldValueRepositoryInterface $orderFieldValueRepository = null,
        QuoteFieldValueRepositoryInterface $quoteFieldValueRepository = null,
        OrderValueCollectionFactory  $orderValueCollectionFactory = null
    ) {

        $this->quoteFieldsCollectionFactory = $quoteFieldsCollectionFactory;
        $this->checkoutSession = $checkoutSession;
        $extensionFactory = $extensionFactory ?? ObjectManager::getInstance()->get(
                ExtensionAttributesFactory::class
            );
        $customAttributeFactory = $customAttributeFactory ?? ObjectManager::getInstance()->get(
                AttributeValueFactory::class
            );
        $this->orderFieldValueRepository = $orderFieldValueRepository ?? ObjectManager::getInstance()->get(
                OrderFieldValueRepositoryInterface::class
            );
        $this->quoteFieldValueRepository = $quoteFieldValueRepository ?? ObjectManager::getInstance()->get(
                QuoteFieldValueRepositoryInterface::class
            );
        $this->orderValueCollectionFactory = $orderValueCollectionFactory ?? ObjectManager::getInstance()->get(
                OrderValueCollectionFactory::class
            );

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
        $this->_init('Digidirect\CheckoutFields\Model\ResourceModel\OrderFieldValue');
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
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
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
     * @return $this
     */
    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @param string|array $value
     * @return $this
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * @param string $fieldId
     * @return $this
     */
    public function setFieldId($fieldId)
    {
        return $this->setData(self::FIELD_ID, $fieldId);
    }

    /**
     * Save quote custom fields to order
     *
     * @param Quote $quote
     * @param Order $order
     * @return void
     * @deprecated 2.0
     */
    public function saveCustomCheckoutValuesToOrder(Quote $quote, Order $order)
    {
        $this->orderFieldValueRepository->moveCheckoutFieldsToOrderFromQuote($quote, $order);
    }

    /**
     * @param $quoteId
     *
     * @return ExtensibleDataInterface[]
     * @deprecated 2.0
     */
    public function prepareQuoteCollectionFields($quoteId)
    {
        return $this->quoteFieldValueRepository->getListByQuoteId($quoteId);
    }

    /**
     * @param ResourceModel\QuoteFieldValue\Collection $items
     * @throws Exception
     * @deprecated 2.0
     */
    public function deleteItems($items)
    {
        /**
         * @var $item QuoteFieldValue
         */
        foreach ($items as $item) {
            $this->quoteFieldValueRepository->delete($item);
        }
    }

    /**
     * Get custom fields order
     *
     * @param $orderId
     *
     * @return mixed
     */
    public function getCustomFields($orderId)
    {
        $collection = $this->orderValueCollectionFactory->create();

        return $collection->addFieldToFilter(self::ORDER_ID, ['eq' => $orderId]);
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
    public function setExtensionAttributes(OrderFieldValueExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
