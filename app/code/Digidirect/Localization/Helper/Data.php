<?php

namespace Digidirect\Localization\Helper;

use Magento\Directory\Model\ResourceModel\Region\Collection as RegionCollection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;
use Digidirect\Utilities\Model\System\Config\Backend\DefaultOptionModel;

class Data extends AbstractHelper
{
    const XML_PATH_TIMEZONE_MATRIX = 'digidirect_localization/timezone/matrix';
    const XML_PATH_HOLIDAY_MATRIX = 'digidirect_localization/holiday/matrix';
    const XML_PATH_PHONE_SUGGESTION = 'digidirect_localization/localization/use_phone_prefix';
    const XML_PATH_LENGTH_UNIT = 'digidirect_localization/localization/length_unit';

    /**
     * @var DefaultOptionModel
     */
    protected $defaultOptionModel;

    /**
     * @var RegionCollectionFactory
     */
    protected $regionCollectionFactory;

    /**
     * @var array
     */
    protected $cacheData = [];

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param DefaultOptionModel $defaultOptionModel
     * @param RegionCollectionFactory $regionCollectionFactory
     */
    public function __construct(
        Context $context,
        DefaultOptionModel $defaultOptionModel,
        RegionCollectionFactory $regionCollectionFactory
    ) {
        parent::__construct($context);
        $this->defaultOptionModel = $defaultOptionModel;
        $this->regionCollectionFactory = $regionCollectionFactory;
    }

    /**
     * @param string $country
     * @param string|null $state
     * @return string|null
     */
    public function getTimezone($country, $state = null)
    {
        return $this->getCountryStateValue(
            self::XML_PATH_TIMEZONE_MATRIX,
            $country,
            $state,
            'timezone_column'
        );
    }

    /**
     * @param string $scope
     * @return string
     */
    public function getLengthUnit($scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_LENGTH_UNIT, $scope);
    }

    /**
     * @param string $country
     * @param string|null $state
     * @return array
     */
    public function getHolidays($country, $state = null)
    {
        $holidays = $this->getCountryStateValue(
            self::XML_PATH_HOLIDAY_MATRIX,
            $country,
            $state,
            'holiday_column'
        );
        if(empty($holidays))
        {
            return '';
        }
        else 
        {
            return array_filter(array_map('trim', explode(',', $holidays)));
        }
        
    }

    /**
     * @param string $configPath
     * @param string $scope
     * @return array
     */
    protected function getMatrixConfig($configPath, $scope = ScopeInterface::SCOPE_WEBSITE)
    {
        $matrixConfig = [];
        $matrixConfigValue = $this->scopeConfig->getValue($configPath, $scope);
        if (!empty($matrixConfigValue)) {
            $matrixConfigRows = $this->defaultOptionModel->convertValueToArray($matrixConfigValue);
            foreach ($matrixConfigRows as $matrixConfigRow) {
                $matrixConfig[] = new DataObject($matrixConfigRow);
            }
        }
        return $matrixConfig;
    }

    /**
     * @param string $configPath
     * @param string $country
     * @param string $state
     * @param string $columnName
     * @param string $scope
     * @param bool $reload
     * @return string|null
     */
    protected function getCountryStateValue(
        $configPath,
        $country,
        $state,
        $columnName,
        $scope = ScopeInterface::SCOPE_WEBSITE,
        $reload = false
    ) {
        $cacheKey = implode('_', func_get_args());
        if (!array_key_exists($cacheKey, $this->cacheData) || $reload) {
            $this->cacheData[$cacheKey] = null;
            $matrix = $this->getMatrixConfig($configPath, $scope);

            if (is_string($state)) {
                /** @var RegionCollection $regionCollection */
                $regionCollection = $this->regionCollectionFactory->create()
                    ->addRegionCodeOrNameFilter($state);

                $state = $regionCollection->getFirstItem()->getId();
            }

            foreach ($matrix as $row) {
                if ($row->getCountryColumn() == $country) {
                    if ($state == $row->getStateColumn()) {
                        $this->cacheData[$cacheKey] = $row->getData($columnName);
                        return $this->cacheData[$cacheKey];
                    }
                    if (empty($row->getStateColumn())) {
                        $this->cacheData[$cacheKey] = $row->getData($columnName);
                    }
                }
            }
        }

        return $this->cacheData[$cacheKey];
    }

    /**
     * @return bool
     */
    public function isPhoneSuggestionEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_PHONE_SUGGESTION);
    }
}
