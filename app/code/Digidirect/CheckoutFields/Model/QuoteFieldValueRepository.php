<?php

namespace Digidirect\CheckoutFields\Model;

use Digidirect\CheckoutFields\Api\Data\QuoteFieldValueInterface;
use Digidirect\CheckoutFields\Api\QuoteFieldValueRepositoryInterface;
use Digidirect\CheckoutFields\Helper\Config;
use Digidirect\CheckoutFields\Helper\Xml\Fields\Parser;
use Digidirect\CheckoutFields\Model\Component\Type\AbstractType;
use Digidirect\CheckoutFields\Model\ResourceModel\QuoteFieldValue as QuoteFieldValueResource;
use Digidirect\CheckoutFields\Model\ResourceModel\QuoteFieldValue\CollectionFactory;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Class QuoteFieldValueRepository
 *
 * @package Digidirect\CheckoutFields\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteFieldValueRepository implements QuoteFieldValueRepositoryInterface
{
    /**
     * @var QuoteFieldValueResource
     */
    protected $resource;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var QuoteFieldValueInterface[]
     */
    protected $quoteRegistry;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var Copy
     */
    protected $copyService;

    /**
     * QuoteFieldValueRepository constructor.
     *
     * @param QuoteFieldValueResource       $resource
     * @param CollectionFactory             $collectionFactory
     * @param Parser                        $parser
     * @param CollectionProcessorInterface  $collectionProcessor
     * @param SearchCriteriaBuilder         $searchCriteriaBuilder
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param Copy                          $copyService
     */
    public function __construct(
        QuoteFieldValueResource $resource,
        CollectionFactory $collectionFactory,
        Parser $parser,
        CollectionProcessorInterface $collectionProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SearchResultsInterfaceFactory $searchResultsFactory,
        Copy $copyService
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->parser = $parser;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchResultsFactory = $searchResultsFactory;
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
    public function getListByQuoteId($quoteId, bool $fromCache = self::LOAD_FROM_CACHE)
    {
        if ($fromCache && isset($this->quoteRegistry[$quoteId])) {
            return $this->quoteRegistry[$quoteId];
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(QuoteFieldValueInterface::QUOTE_ID, $quoteId)
            ->create();

        $this->quoteRegistry[$quoteId] = $this->getList($searchCriteria)->getItems();

        return $this->quoteRegistry[$quoteId];
    }

    /**
     * @param array $quoteFieldValues
     *
     * @return array
     * @throws CouldNotSaveException
     */
    public function saveAll(array $quoteFieldValues)
    {
        try {
            $this->resource->saveCustomFieldsValuesToQuote($quoteFieldValues);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the Custom Fields: %1', $exception->getMessage()
                )
            );
        }

        return $quoteFieldValues;
    }

    /**
     * {@inheritDoc}
     */
    public function saveToQuote(CartInterface $quote, $params = [], $reSave = true)
    {
        if ($reSave) {
            $this->cleanDataBeforeSave($quote->getId());
        }
        $fields = $this->parser->getFields($quote->getStoreId());

        $dataToSave = [];
        foreach ($params as $code => $values) {
            if (!isset($fields[$code])) {
                continue;
            }
            $dataToSave[$code] = $this->mapFieldValues($code, $values, $fields);
            $dataToSave[$code] = $this->setQuoteIdToFieldValues($quote, $dataToSave[$code]);
        }
        if ($dataToSave) {
            $this->saveAll($dataToSave);

            return true;
        }

        return false;
    }

    /**
     * @param QuoteFieldValueInterface $quoteFieldValue
     *
     * @return bool|void
     * @throws CouldNotSaveException
     */
    public function delete(QuoteFieldValueInterface $quoteFieldValue)
    {
        try {
            /** @var QuoteFieldValue $quoteFieldValue */
            $this->resource->delete($quoteFieldValue);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not delete the Custom Field: %1', $e->getMessage()
                )
            );
        }
    }

    /**
     * @param $quoteId
     * @param $fieldId
     *
     * @return ExtensibleDataInterface[]
     */
    public function getCustomField($quoteId, $fieldId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(QuoteFieldValueInterface::QUOTE_ID, $quoteId)
            ->addFilter(QuoteFieldValueInterface::FIELD_ID, $fieldId)
            ->create();

        return $this->getList($searchCriteria)->getItems();
    }

    /**
     * Delete quote values after saving to order
     *
     * @param int $quoteId
     *
     * @return void
     * @throws CouldNotSaveException
     */
    protected function cleanDataBeforeSave($quoteId)
    {
        $items = $this->getListByQuoteId($quoteId);
        /** @var QuoteFieldValueInterface $item */
        foreach ($items as $item) {
            $this->delete($item);
        }
    }

    /**
     * @param CartInterface $quote
     * @param array         $values
     *
     * @return array
     */
    protected function setQuoteIdToFieldValues(CartInterface $quote, array $values): array
    {
        return $this->copyService->copyFieldsetToTarget(
            Config::FIELDSET_ID_QUOTE,
            Config::FIELDSET_ASPECT_TO_QUOTE,
            $quote,
            $values
        );
    }

    /**
     * @param $code
     * @param $values
     * @param $fields
     *
     * @return array
     */
    protected function mapFieldValues($code, $values, $fields): array
    {
        if ($values instanceof QuoteFieldValueInterface) {
            $values = $values->getValue();
        }
        $label = $fields[$code][AbstractType::XML_FRONTEND_NAME] ?? '';

        return [
            QuoteFieldValueInterface::CODE => $label,
            QuoteFieldValueInterface::VALUE => serialize($values ?? ''),
            QuoteFieldValueInterface::FIELD_ID => $code,
        ];
    }
}
