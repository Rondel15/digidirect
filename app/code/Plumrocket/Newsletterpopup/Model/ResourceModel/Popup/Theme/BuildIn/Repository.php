<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme\BuildIn;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsFactory;
use Magento\Framework\Api\SearchResultsInterface;
use Plumrocket\Newsletterpopup\Api\BuildInThemeRepositoryInterface;
use Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface;
use Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme as ThemeResource;
use Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme\CollectionFactory;

/**
 * @since 4.0.0
 */
class Repository implements BuildInThemeRepositoryInterface
{
    /**
     * @var \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme
     */
    private $themeResource;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme\CollectionFactory
     */
    private $themeCollectionFactory;

    /**
     * @var \Magento\Framework\Api\SearchResultsFactory
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme                   $themeResource
     * @param \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme\CollectionFactory $themeCollectionFactory
     * @param \Magento\Framework\Api\SearchResultsFactory                                   $searchResultsFactory
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface            $collectionProcessor
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                                  $searchCriteriaBuilder
     */
    public function __construct(
        ThemeResource $themeResource,
        CollectionFactory $themeCollectionFactory,
        SearchResultsFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->themeResource = $themeResource;
        $this->themeCollectionFactory = $themeCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function save(PopupThemeInterface $theme): PopupThemeInterface
    {
        $theme->setCanSaveBaseTemplates(true);
        $theme->setData('base_template_id', -1);
        $this->themeResource->save($theme);

        return $theme;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null): SearchResultsInterface
    {
        /** @var \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme\Collection $collection */
        $collection = $this->themeCollectionFactory->create();
        $collection->addBuildInFilter();
        if ($searchCriteria) {
            $this->collectionProcessor->process($searchCriteria, $collection);
        } else {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        }
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
