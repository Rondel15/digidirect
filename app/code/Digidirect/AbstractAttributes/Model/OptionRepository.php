<?php

namespace Digidirect\AbstractAttributes\Model;

use Digidirect\AbstractAttributes\Api\Data\OptionInterface;
use Digidirect\AbstractAttributes\Api\Data\OptionInterfaceFactory;
use Digidirect\AbstractAttributes\Api\Data\OptionSearchResultsInterfaceFactory;
use Digidirect\AbstractAttributes\Helper\Image as ImageHelper;
use Digidirect\AbstractAttributes\Helper\ImageFactory as ImageHelperFactory;
use Digidirect\AbstractAttributes\Model\Media\ImageProcessorFactory;
use Magento\Catalog\Api\ProductAttributeOptionManagementInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Eav\Api\Data\AttributeOptionLabelInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;

/**
 * Class OptionRepository
 *
 * @package Digidirect\AbstractAttributes\Model
 */
class OptionRepository implements \Digidirect\AbstractAttributes\Api\OptionRepositoryInterface
{
    use AbstractRepositoryTrait;

    /**
     * @var \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface[]
     */
    protected $optionAttributes = [];

    /**
     * @var OptionFactory
     */
    protected $optionFactory;

    /**
     * @var OptionSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var ResourceModel\Option\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ResourceModel\Option
     */
    protected $resourceModel;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $productAttributeRepository;

    /**
     * @var AttributeOptionInterfaceFactory
     */
    protected $attributeOptionInterfaceFactory;

    /**
     * @var ProductAttributeOptionManagementInterfaceFactory
     */
    protected $productAttributeOptionManagementFactory;

    /**
     * @var AttributeOptionLabelInterfaceFactory
     */
    protected $attributeOptionLabelFactory;

    /**
     * @var ImageHelperFactory
     */
    protected $imageHelperFactory;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var ImageProcessorFactory
     */
    protected $imageProcessorFactory;

    /**
     * @var AbstractAttributeRepository
     */
    protected $abstractAttributeRepository;

    /**
     * OptionRepository constructor.
     *
     * @param OptionInterfaceFactory $optionFactory
     * @param OptionSearchResultsInterfaceFactory $searchResultsFactory
     * @param ResourceModel\Option $resourceModel
     * @param ResourceModel\Option\CollectionFactory $collectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param ProductAttributeOptionManagementInterfaceFactory $productAttributeOptionManagementFactory
     * @param AttributeOptionInterfaceFactory $attributeOptionInterfaceFactory
     * @param AttributeOptionLabelInterfaceFactory $attributeOptionLabelFactory
     * @param ImageHelperFactory $imageHelperFactory
     * @param ImageProcessorFactory $imageProcessorFactory
     * @param AbstractAttributeRepository $abstractAttributeRepository
     */
    public function __construct(
        OptionInterfaceFactory $optionFactory,
        OptionSearchResultsInterfaceFactory $searchResultsFactory,
        ResourceModel\Option $resourceModel,
        ResourceModel\Option\CollectionFactory $collectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        ProductAttributeOptionManagementInterfaceFactory $productAttributeOptionManagementFactory,
        AttributeOptionInterfaceFactory $attributeOptionInterfaceFactory,
        AttributeOptionLabelInterfaceFactory $attributeOptionLabelFactory,
        ImageHelperFactory $imageHelperFactory,
        ImageProcessorFactory $imageProcessorFactory,
        AbstractAttributeRepository $abstractAttributeRepository
    ) {
        $this->optionFactory = $optionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->resourceModel = $resourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productAttributeOptionManagementFactory = $productAttributeOptionManagementFactory;
        $this->attributeOptionInterfaceFactory = $attributeOptionInterfaceFactory;
        $this->attributeOptionLabelFactory = $attributeOptionLabelFactory;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->imageProcessorFactory = $imageProcessorFactory;
        $this->abstractAttributeRepository = $abstractAttributeRepository;
    }

    /**
     * @param int $id
     * @param bool $forceReload
     * @return \Digidirect\AbstractAttributes\Api\Data\OptionInterface
     * @throws NoSuchEntityException
     */
    public function get($id, $forceReload = false)
    {
        if (!isset($this->instances[$id]) || $forceReload) {
            /** @var \Digidirect\AbstractAttributes\Api\Data\OptionInterface $option */
            $option = $this->optionFactory->create();
            $this->resourceModel->load($option, $id);
            if (!$option->getId()) {
                throw new NoSuchEntityException(__('Requested option doesn\'t exist'));
            }
            $this->instances[$id] = $option;
        }
        return $this->instances[$id];
    }

    /**
     * @param int $optionId
     * @param null $storeId
     * @param bool $forceReload
     * @return \Digidirect\AbstractAttributes\Api\Data\OptionInterface
     * @throws NoSuchEntityException
     */
    public function getByOptionId($optionId, $storeId = null, $forceReload = false)
    {
        $cacheKey = $optionId . '_' . $storeId;
        if (!isset($this->instances[$cacheKey]) || $forceReload) {
            /** @var \Digidirect\AbstractAttributes\Api\Data\OptionInterface $option */
            $option = $this->optionFactory->create();
            if ($storeId !== null) {
                $option->setStoreId($storeId);
            }
            $this->resourceModel->load($option, $optionId, OptionInterface::OPTION_ID);
            if (!$option->getOptionId()) {
                throw new NoSuchEntityException(__('Requested option doesn\'t exist'));
            }
            $this->instances[$cacheKey] = $option;
        }
        return $this->instances[$cacheKey];
    }

    /**
     * {@inheritdoc}
     */
    public function getAbstractAttribute($optionId, $storeId = null)
    {
        if (!isset($this->optionAttributes[$optionId])) {
            $this->optionAttributes[$optionId] = $this->resourceModel->getAttributeId($optionId);
        }

        return $this->abstractAttributeRepository->getByAttributeId($this->optionAttributes[$optionId], $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Digidirect\AbstractAttributes\Api\Data\OptionInterface $option)
    {
        $data = $option->getData();
        if (empty($data['attribute_id']) && empty($data['attribute_code'])) {
            throw new NoSuchEntityException(__('Attribute not found'));
        }

        if (empty($data['attribute_code'])) {
            $data['attribute_code'] = $this->_getAttributeCode($data['attribute_id']);
        }

        if (!$attributeCode = $data['attribute_code']) {
            throw new NoSuchEntityException(__('Attribute not found'));
        }

        if (empty($data['url_key'])) {
            $option->setUrlKey($option->getLabel());
        }

        $data['url_key'] = $this->_processUrlKey($option);

        $optionId = $option->getOptionId();
        $storeId = $option->getStoreId();

        $attribute = $this->productAttributeRepository->get($attributeCode);
        $aAttribute = $this->abstractAttributeRepository->getByAttributeId($data['attribute_id']);
        if ($optionId && $attribute->getAttributeId() != $aAttribute->getAttributeId()) {
            throw new CouldNotSaveException(
                __('This option is not belong to the attribute %1.', $data['attribute_code'])
            );
        }

        try {
            if (!$this->resourceModel->isAdminLabelUnique($option)) {
                throw new CouldNotSaveException(__('Admin Label value must be unique.'));
            }

            $productOption = $this->attributeOptionInterfaceFactory->create();
            if ($optionId) {
                $productOption->setValue($optionId);
            } else {
                /** @see \Digidirect\AbstractAttributes\Model\ResourceModel\Entity\Attribute::_updateAttributeOption */
                $attribute->setSaveAdvancedOption(true);
            }

            $productOption->setIsDefault($data['is_default']);
            $productOption->setSortOrder($data['position']);
            $labels = $this->_processLabels($data);
            if (!empty($labels)) {
                $productOption->setStoreLabels($labels);
            }

            /** @see \Digidirect\AbstractAttributes\Model\ResourceModel\AbstractAttribute::_afterSave */
            $attribute->setSkipAaSave(true);

            /** @var \Magento\Catalog\Api\ProductAttributeOptionManagementInterface $productAttributeOptionManagement */
            $productAttributeOptionManagement = $this->productAttributeOptionManagementFactory->create();
            $productAttributeOptionManagement->add($attributeCode, $productOption);

            $data['listing'] = isset($data['listing']) ? $data['listing'] : 1;

            if ($optionId) {
                try {
                    $option = $this->getByOptionId($optionId, $storeId);
                    if ($option->getStoreId() != $storeId) {
                        $option->isObjectNew(true);
                    }
                } catch (NoSuchEntityException $e) {
                    $option->isObjectNew(true);
                }

                $option->addData($data);
            } else {
                /** @see \Digidirect\AbstractAttributes\Model\ResourceModel\Entity\Attribute::_updateAttributeOption */
                $optionId = $attribute->getAdvancedOptionId();
                if (!$optionId) {
                    throw new \Exception(__('Option not found'));
                }

                $data['option_id'] = $optionId;
                $option->setData($data);
                $option->isObjectNew(true);
            }

            $images = $this->_processImages($option);
            $currentDate = new \DateTime();
            $option->setAaOptionUpdatedAt(
                $currentDate->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
            );
            $this->resourceModel->save($option);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save option'));
        }

        if (!empty($images)) {
            $this->getImageHelper()->deleteOldImage($images);
        }

        return $this->getByOptionId($optionId, $storeId, true);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Digidirect\AbstractAttributes\Api\Data\OptionInterface $option)
    {
        $id = $option->getId();

        try {
            unset($this->instances[$id]);
            $this->resourceModel->delete($option);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove abstract attribute option ID%1', $id)
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id)
    {
        try {
            $option = $this->getByOptionId($id);
            $this->delete($option);
        } catch (NoSuchEntityException $nse) {
            $deleteNatOption = true;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove abstract attribute option ID%1', $id)
            );
        }

        if (isset($deleteNatOption)) {
            try {
                /** @var \Digidirect\AbstractAttributes\Model\Option $option */
                $option = $this->optionFactory->create();
                $option->deleteAttributeOption(null, $id);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\StateException(
                    __('Unable to remove abstract attribute option ID%1', $id)
                );
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->getCollection();
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->_addFilterGroupToCollection($group, $collection);
        }

        /** @var SortOrder $sortOrder */
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $field = $sortOrder->getField();
            $collection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
            );
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->load();

        $items = $collection->getItems();

        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($items);
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * Get options by attribute ID.
     *
     * @param int $attributeId
     * @param int $storeId
     * @return \Digidirect\AbstractAttributes\Api\Data\OptionInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException If aa with the option ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeOptions($attributeId, $storeId = null)
    {
        $collection = $this->getCollection()
            ->addAttributeFilter($attributeId);

        if ($storeId !== null) {
            $collection->addStoreFilter($storeId);
        }

        return $collection->getItems();
    }

    /**
     * Get collection
     *
     * @return ResourceModel\Option\Collection
     */
    public function getCollection()
    {
        /** @var ResourceModel\Option\Collection $collection */
        $collection = $this->collectionFactory->create();
        return $collection;
    }

    /**
     * Get attribute code by id
     *
     * @param int $attributeId
     * @return string
     */
    protected function _getAttributeCode($attributeId)
    {
        return $this->resourceModel->getAttributeCode($attributeId);
    }

    /**
     * Process option labels
     *
     * @param array $data
     * @return array
     */
    protected function _processLabels(array $data)
    {
        $labels = [];
        foreach ($data as $k => $value) {
            if (strpos($k, 'label_') !== false) {
                /** @var AttributeOptionLabelInterface $label */
                $label = $this->attributeOptionLabelFactory->create();
                $label->setData([
                    AttributeOptionLabelInterface::LABEL => $value,
                    AttributeOptionLabelInterface::STORE_ID => $this->_getStoreId($k),
                ]);
                $labels[] = $label;
            }
        }
        return $labels;
    }

    /**
     * Process option images
     *
     * @param \Digidirect\AbstractAttributes\Model\Option $option
     * @return string[]|null
     * @throws LocalizedException
     */
    protected function _processImages($option)
    {
        $image = null;

        foreach (ImageHelper::IMAGES_INFO as $key => $infoKey) {
            if ($imageFile = $this->getImageHelper()->getImageInfo($option, $infoKey)) {
                $result = $this->imageProcessorFactory->create()->save($imageFile);
                //Result can be empty array and it mean that image stay the same
                if (isset($result['error'])) {
                    throw new LocalizedException($result['error']);
                } elseif (!empty($result['file'])) {
                    // Remember old image and delete earlier
                    if ($option->hasData($key) && ($img = $option->getData($key)) && $img != $result['file']) {
                        $image[] = $img;
                    }
                    $option->setData($key, $result['file']);
                }
            } else { // It mean that image was deleted or it wasn't uploaded
                if ($img = $option->getData($key)) {
                    $image[] = $img;
                }

                $option->setData($key, null);
            }
        }

        return $image;
    }

    /**
     * Get store id
     *
     * @param string $data
     * @return string
     */
    protected function _getStoreId($data)
    {
        $id = substr($data, strrpos($data, '_') + 1);
        return $id;
    }

    /**
     * Validate url key
     *
     * @param string $urlKey
     * @return true If url key is valid
     * @throws ValidatorException
     */
    protected function _validateUrlKey($urlKey)
    {
        return true;
    }

    /**
     * Get image helper
     *
     * @return ImageHelper
     */
    protected function getImageHelper()
    {
        if (null === $this->imageHelper) {
            $this->imageHelper = $this->imageHelperFactory->create();
        }
        return $this->imageHelper;
    }
}
