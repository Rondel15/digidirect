<?php
namespace Digidirect\LayeredNavigation\Model;

use Digidirect\LayeredNavigation\Api\FilterSettingRepositoryInterface;
use Digidirect\LayeredNavigation\Api\Data\FilterSettingInterface;
use Digidirect\LayeredNavigation\Model\ResourceModel\FilterSetting as ResourceFilter;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class FilterSettingRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FilterSettingRepository implements FilterSettingRepositoryInterface
{
    /**
     * @var ResourceFilter
     */
    protected $_resource;

    /**
     * @var FilterSettingInterface
     */
    protected $_filterSettingFactory;

    /**
     * @param ResourceFilter $resource
     * @param FilterSettingFactory $filterSettingFactory
     */
    public function __construct(
        ResourceFilter $resource,
        FilterSettingFactory $filterSettingFactory
    ) {
        $this->_resource = $resource;
        $this->_filterSettingFactory = $filterSettingFactory;
    }

    /**
     * Save Filter Setting data
     *
     * @param FilterSettingInterface $filterSetting
     * @return FilterSettingInterface
     * @throws CouldNotSaveException
     */
    public function save(FilterSettingInterface $filterSetting)
    {
        try {
            $this->_resource->save($filterSetting);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('We can\'t save the Filter Setting.'), $exception);
        }
        return $filterSetting;
    }

    /**
     * Load Filter Setting data by given Identity
     *
     * @param int $filterSettingId
     * @return FilterSettingInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($filterSettingId)
    {
        $filterSetting = $this->_filterSettingFactory->create();
        $this->_resource->load($filterSetting, $filterSettingId);
        if (!$filterSetting->getId()) {
            throw new NoSuchEntityException(__('Filter Setting with id "%1" does not exist.', $filterSettingId));
        }
        return $filterSetting;
    }

    /**
     * Load Filter Setting data by given filter code
     *
     * @param string $filterCode
     * @return FilterSettingInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadByFilterCode($filterCode)
    {
        $filterSetting = $this->_filterSettingFactory->create();
        $this->_resource->load($filterSetting, 'attr_' . $filterCode, FilterSettingInterface::FILTER_CODE);
        return $filterSetting;
    }

    /**
     * Delete Filter Setting
     *
     * @param FilterSettingInterface $filterSetting
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(FilterSettingInterface $filterSetting)
    {
        try {
            $this->_resource->delete($filterSetting);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('We can\'t delete the Filter Setting.'), $exception);
        }
        return true;
    }

    /**
     * Delete Filter Setting by given Identity
     *
     * @param int $filterSettingId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($filterSettingId)
    {
        return $this->delete($this->getById($filterSettingId));
    }
}
