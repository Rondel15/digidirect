<?php

namespace Digidirect\CheckoutFields\Model;

use Digidirect\CheckoutFields\Api\Data\OrderFieldValueInterface;
use Digidirect\CheckoutFields\Api\Data\QuoteFieldValueInterface;
use Digidirect\CheckoutFields\Api\OrderFieldValueRepositoryInterface;
use Digidirect\CheckoutFields\Api\QuoteFieldValueRepositoryInterface;
use Digidirect\CheckoutFields\Helper\Config;
use Digidirect\CheckoutFields\Model\ResourceModel\OrderFieldValue as OrderFieldValueResource;
use Digidirect\CheckoutFields\Model\ResourceModel\OrderFieldValue\CollectionFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\DataObject\Copy;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class OrderFieldValueRepository
 * @package Digidirect\CheckoutFields\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderFieldValueRepository implements OrderFieldValueRepositoryInterface
{
    /**
     * @var OrderFieldValueResource
     */
    protected $resource;

    /**
     * @var QuoteFieldValueFactory
     */
    protected $modelFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var QuoteFieldValueRepositoryInterface
     */
    protected $quoteFieldValueRepository;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var OrderFieldValueInterface[]
     */
    protected $orderValueRegistry;

    /**
     * @var Copy
     */
    protected $copyService;

    /**
     * QuoteFieldValueRepository constructor.
     *
     * @param QuoteFieldValueFactory             $modelFactory
     * @param OrderFieldValueResource            $resource
     * @param CollectionFactory                  $collectionFactory
     * @param SearchCriteriaBuilder              $searchCriteriaBuilder
     * @param SearchResultsInterfaceFactory      $searchResultsFactory
     * @param SortOrderBuilder                   $sortOrderBuilder
     * @param CollectionProcessorInterface       $collectionProcessor
     * @param QuoteFieldValueRepositoryInterface $quoteFieldValueRepository
     * @param Session                            $checkoutSession
     * @param Copy                               $copyService
     */
    public function __construct(
        QuoteFieldValueFactory $modelFactory,
        OrderFieldValueResource $resource,
        CollectionFactory $collectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SearchResultsInterfaceFactory $searchResultsFactory,
        SortOrderBuilder $sortOrderBuilder,
        CollectionProcessorInterface $collectionProcessor,
        QuoteFieldValueRepositoryInterface $quoteFieldValueRepository,
        Session $checkoutSession,
        Copy $copyService
    ) {
        $this->modelFactory = $modelFactory;
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->collectionProcessor = $collectionProcessor;
        $this->quoteFieldValueRepository = $quoteFieldValueRepository;
        $this->checkoutSession = $checkoutSession;
        $this->copyService = $copyService;
    }

    /**
     * @param SearchCriteriaInterface $criteria
     *
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * {@inheritDoc}
     */
    public function getListByOrderId($orderId, bool $fromCache = self::LOAD_FROM_CACHE)
    {
        if ($fromCache && isset($this->orderValueRegistry[$orderId])) {
            return $this->orderValueRegistry[$orderId];
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(OrderFieldValueInterface::ORDER_ID, $orderId)
            ->create();
        $this->orderValueRegistry[$orderId] = $this->getList($searchCriteria)->getItems();

        return $this->orderValueRegistry[$orderId];
    }

    /**
     * {@inheritDoc}
     */
    public function moveCheckoutFieldsToOrderFromQuote(CartInterface $quote, OrderInterface $order)
    {
        $quoteFieldValues = $this->quoteFieldValueRepository->getListByQuoteId(
            $quote->getId(),
            !QuoteFieldValueRepositoryInterface::LOAD_FROM_CACHE
        );

        $this->setObserverFlag($quoteFieldValues);

        $orderFieldData = [];
        foreach ($quoteFieldValues as $key => $item) {
            $orderFieldData[$key] = $this->mapFieldValues($order, $item);
        }

        if (empty($orderFieldData)) {
            return false;
        }

        $this->resource->saveCustomCheckoutValuesToOrder($orderFieldData);
        foreach ($quoteFieldValues as $quoteFieldValue) {
            $this->quoteFieldValueRepository->delete($quoteFieldValue);
        }

        return true;
    }

    /**
     * @param OrderInterface           $order
     * @param QuoteFieldValueInterface $item
     *
     * @return array
     */
    protected function mapFieldValues(OrderInterface $order, QuoteFieldValueInterface $item): array
    {
        $orderFieldData = [];
        $orderFieldData = $this->copyService->copyFieldsetToTarget(
            Config::FIELDSET_ID_ORDER,
            Config::FIELDSET_ASPECT_FROM_QUOTE,
            $item,
            $orderFieldData
        );
        $orderFieldData = $this->copyService->copyFieldsetToTarget(
            Config::FIELDSET_ID_ORDER,
            Config::FIELDSET_ASPECT_FROM_ORDER,
            $order,
            $orderFieldData
        );

        return $orderFieldData;
    }

    /**
     * TODO Obsolete flag?
     * @param $quoteFieldValues
     *
     * @return $this
     */
    protected function setObserverFlag($quoteFieldValues)
    {
        if (!count($quoteFieldValues)) {
            // Index controller was not fired yet
            $this->checkoutSession->setObserverFlag(true);
        }

        return $this;
    }
}
