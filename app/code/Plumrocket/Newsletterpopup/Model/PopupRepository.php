<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model;

use Exception;
use Magento\Eav\Model\Entity\Attribute\Exception as AttributeException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Plumrocket\Newsletterpopup\Api\Data\PopupInterface;
use Plumrocket\Newsletterpopup\Api\Data\PopupInterfaceFactory;
use Plumrocket\Newsletterpopup\Api\Data\PopupSearchResultsInterfaceFactory;
use Plumrocket\Newsletterpopup\Api\PopupRepositoryInterface;
use Plumrocket\Newsletterpopup\Model\ResourceModel\Popup as PopupResource;
use Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\CollectionFactory;

/**
 * @since 1.0.0
 */
class PopupRepository implements PopupRepositoryInterface
{
    private $instancesById = [];

    /**
     * @var \Plumrocket\Newsletterpopup\Api\Data\PopupInterfaceFactory
     */
    private $modelFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Api\Data\PopupSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup
     */
    private $resourceModel;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface      $collectionProcessor
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupInterfaceFactory              $popupFactory
     * @param \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup                   $popupResource
     */
    public function __construct(
        PopupSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        PopupInterfaceFactory $popupFactory,
        PopupResource $popupResource
    ) {
        $this->modelFactory = $popupFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->resourceModel = $popupResource;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(PopupInterface $popup): PopupInterface
    {
        try {
            $this->clearLocalCache($popup);
            $this->resourceModel->save($popup);
        } catch (AttributeException $exception) {
            throw InputException::invalidFieldValue(
                $exception->getAttributeCode(),
                $popup->getData($exception->getAttributeCode()),
                $exception
            );
        } catch (Exception $e) {
            throw new CouldNotSaveException(
                __('The popup was unable to be saved. Please try again.'),
                $e
            );
        }

        $this->clearLocalCache($popup);

        return $this->getById((int) $popup->getId());
    }

    /**
     * @inheritDoc
     */
    public function getById(int $popupId, bool $forceReload = false): PopupInterface
    {
        if (! isset($this->instancesById[$popupId]) || $forceReload) {
            /** @var \Plumrocket\Newsletterpopup\Model\Popup|PopupInterface $popup */
            $popup = $this->modelFactory->create();
            $this->resourceModel->load($popup, $popupId);
            if (! $popup->getId()) {
                throw new NoSuchEntityException(
                    __("The popup that was requested doesn't exist. Verify the popup and try again.")
                );
            }

            $this->instancesById[$popup->getId()] = $popup;
        }

        return $this->instancesById[$popupId];
    }

    /**
     * @inheritDoc
     */
    public function delete(PopupInterface $popup): bool
    {
        $popupId = $popup->getId();
        try {
            $this->clearLocalCache($popup);
            $this->resourceModel->delete($popup);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (Exception $e) {
            throw new StateException(
                __('The "%1" popup couldn\'t be removed.', $popupId)
            );
        }

        unset($this->instancesById[$popupId]);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($id): bool
    {
        return $this->delete($this->getById($id));
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        /** @var \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addThemeData();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \Plumrocket\Newsletterpopup\Api\Data\PopupSearchResultsInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupInterface $popup
     * @return \Plumrocket\Newsletterpopup\Api\PopupRepositoryInterface
     */
    private function clearLocalCache(PopupInterface $popup): PopupRepositoryInterface
    {
        unset($this->instancesById[$popup->getId()]);
        return $this;
    }
}
