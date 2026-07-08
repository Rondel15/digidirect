<?php
namespace Digidirect\LayeredNavigation\Model;

use Digidirect\LayeredNavigation\Api\Data\FilterSettingInterface;
use Digidirect\LayeredNavigation\Model\Source\DisplayMode;
use Magento\Framework\DataObject\IdentityInterface;

class FilterSetting extends \Magento\Framework\Model\AbstractModel implements FilterSettingInterface, IdentityInterface
{
    /**
     * CACHE_TAG
     */
    const CACHE_TAG = 'layered_navigation_filter_setting';

    /**
     * @var string
     */
    protected $_eventPrefix = 'layered_navigation_filter_setting';

    /**
     * Construct
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Digidirect\LayeredNavigation\Model\ResourceModel\FilterSetting');
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->getData(self::FILTER_SETTING_ID);
    }

    /**
     * @return mixed
     */
    public function getDisplayMode()
    {
        return $this->getData(self::DISPLAY_MODE);
    }

    /**
     * @return mixed
     */
    public function getFilterCode()
    {
        return $this->getData(self::FILTER_CODE);
    }

    /**
     * @return mixed
     */
    public function getFollowMode()
    {
        return $this->getData(self::FOLLOW_MODE);
    }

    /**
     * @return mixed
     */
    public function getHideOneOption()
    {
        return $this->getData(self::HIDE_ONE_OPTION);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return mixed
     */
    public function getIndexMode()
    {
        return $this->getData(self::INDEX_MODE);
    }

    /**
     * @param string $currencySymbol
     * @return string
     */
    public function getUnitsLabel($currencySymbol = '')
    {
        if ($this->getUnitsLabelUseCurrencySymbol()) {
            return $currencySymbol;
        }

        return parent::getUnitsLabel();
    }

    /**
     * @return bool
     */
    public function isMultiselect()
    {
        return $this->getData(self::IS_MULTISELECT) && $this->isDisplayTypeAllowsMultiselect();
    }

    /**
     * @return bool
     */
    public function useAndLogic()
    {
        return $this->getData(self::USE_AND_LOGIC);
    }

    /**
     * @return bool|null
     */
    public function isShowMoreEnabled()
    {
        return $this->getData(self::SHOW_MORE_ENABLED);
    }

    /**
     * @return int
     */
    public function getShowMoreCount()
    {
        $count = (int)$this->getData(self::SHOW_MORE_COUNT);
        if ($count) {
            return $count;
        }

        return self::DEFAULT_SHOW_MORE_COUNT;
    }

    /**
     * @param int $hideOenOption
     * @return $this
     */
    public function setHideOneOption($hideOenOption)
    {
        return $this->setData(self::HIDE_ONE_OPTION, $hideOenOption);
    }

    /**
     * @param int|mixed $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::FILTER_SETTING_ID, $id);
    }

    /**
     * @param int $displayMode
     * @return $this
     */
    public function setDisplayMode($displayMode)
    {
        return $this->setData(self::DISPLAY_MODE, $displayMode);
    }

    /**
     * @param string $filterCode
     * @return $this
     */
    public function setFilterCode($filterCode)
    {
        return $this->setData(self::FILTER_CODE, $filterCode);
    }

    /**
     * @param int $indexMode
     * @return $this
     */
    public function setIndexMode($indexMode)
    {
        return $this->setData(self::INDEX_MODE);
    }

    /**
     * @param int $followMode
     * @return $this
     */
    public function setFollowMode($followMode)
    {
        return $this->setData(self::FOLLOW_MODE);
    }

    /**
     * @param bool $isMultiselect
     * @return $this
     */
    public function setIsMultiselect($isMultiselect)
    {
        return $this->setData(self::IS_MULTISELECT, $isMultiselect);
    }

    /**
     * @return bool
     */
    protected function isDisplayTypeAllowsMultiselect()
    {
        return $this->getDisplayMode() == DisplayMode::MODE_DEFAULT;
    }

    /**
     * @param bool $useAndLogic
     * @return $this
     */
    public function setUseAndLogic($useAndLogic)
    {
        return $this->setData(self::USE_AND_LOGIC, $useAndLogic);
    }

    /**
     * @param bool $enabled
     * @return FilterSettingInterface
     */
    public function setShowMoreEnabled($enabled)
    {
        return $this->setData(self::SHOW_MORE_ENABLED, $enabled);
    }

    /**
     * @param int $count
     * @return FilterSettingInterface
     */
    public function setShowMoreCount($count)
    {
        return $this->setData(self::SHOW_MORE_COUNT, $count);
    }
}
