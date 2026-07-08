<?php

namespace Digidirect\ShippingAvailabilityCheck\Model;

use Magento\Quote\Model\QuoteRepository as MagentoQuoteRepository;
use Digidirect\ShippingAvailabilityCheck\Api\QuoteRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\QuoteRepository\LoadHandler;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Quote\Model\QuoteFactory as MagentoQuoteFactory;
use Digidirect\ShippingAvailabilityCheck\Model\QuoteFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class QuoteRepository
 * @package Digidirect\ShippingAvailabilityCheck\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteRepository extends MagentoQuoteRepository implements QuoteRepositoryInterface
{
    /**
     * @var LoadHandler
     */
    private $loadHandler;

    /**
     * @var Quote[]
     */
    protected $quotesByProduct = [];

    /**
     * @var \Digidirect\ShippingAvailabilityCheck\Model\QuoteFactory|
     */
    protected $extendedQuoteFactory;

    /**
     * QuoteRepository constructor.
     * @param MagentoQuoteFactory $quoteFactory
     * @param StoreManagerInterface $storeManager
     * @param QuoteCollection $quoteCollection
     * @param \Magento\Quote\Api\Data\CartSearchResultsInterfaceFactory $searchResultsDataFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param \Digidirect\ShippingAvailabilityCheck\Model\QuoteFactory $extendedQuoteFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     * @param QuoteCollectionFactory|null $quoteCollectionFactory
     */
    public function __construct(
        MagentoQuoteFactory $quoteFactory,
        StoreManagerInterface $storeManager,
        QuoteCollection $quoteCollection,
        \Magento\Quote\Api\Data\CartSearchResultsInterfaceFactory $searchResultsDataFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        QuoteFactory $extendedQuoteFactory,
        CollectionProcessorInterface $collectionProcessor = null,
        QuoteCollectionFactory $quoteCollectionFactory = null
    ) {
        parent::__construct(
            $quoteFactory,
            $storeManager,
            $quoteCollection,
            $searchResultsDataFactory,
            $extensionAttributesJoinProcessor,
            $collectionProcessor,
            $quoteCollectionFactory
        );
        $this->extendedQuoteFactory = $extendedQuoteFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getForProduct($hash, $customerId)
    {
        if (!isset($this->quotesByProduct[$hash])) {
            $quote = $this->extendedQuoteFactory->create();
            $quote->setStoreId($this->storeManager->getStore()->getId());
            $quote = $quote->loadByShippingAvailabilityCheckHash($hash, $customerId);
            $this->getLoadHandler()->load($quote);
            if (!$quote->getId()) {
                throw new NoSuchEntityException(__('Quote was not found.'));
            }
            $this->quotesById[$quote->getId()] = $quote;
            $this->quotesByProduct[$hash] = $quote;
        }
        return $this->quotesByProduct[$hash];
    }

    /**
     * @return LoadHandler
     */
    private function getLoadHandler()
    {
        if (!$this->loadHandler) {
            $this->loadHandler = ObjectManager::getInstance()->get(LoadHandler::class);
        }
        return $this->loadHandler;
    }
}
