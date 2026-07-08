<?php

namespace Digidirect\ExtendedShippingRates\Model\Zone;

use Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import\Adapter as ImportAdapter;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\Store\Model\Store;

/**
 * Class Import
 *
 * @package Digidirect\ExtendedShippingRates\Model\Zone
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Import extends \Magento\Framework\Model\AbstractModel
{
    const ZONE_ID_COLUMN_HEAD = 'id';
    const STATE_COLUMN_HEAD = 'state';
    const COUNTRY_COLUMN_HEAD = 'country';
    const ACTIVE_COLUMN_HEAD = 'active';
    const ERROR_VALUE_IS_MANDATORY = 'ValueIsMandatory';
    const ERROR_VALUE_IS_INVALID = 'ValueIsInvalid';
    const ALL_STATE_FLAG = 'ALL';

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\ImportExport\Model\Import\Source\Csv
     */
    protected $_source;

    /**
     * @var \Magento\ImportExport\Model\ResourceModel\Helper
     */
    protected $_resourceHelper;

    /**
     * Import export data
     *
     * @var \Magento\ImportExport\Helper\Data
     */
    protected $_importExportData;

    /**
     * DB data source model.
     *
     * @var \Magento\ImportExport\Model\ResourceModel\Import\Data
     */
    protected $_dataSourceModel;

    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface
     */
    protected $_errorAggregator;

    /**
     * Json Helper
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * Number of rows processed by validation.
     *
     * @var int
     */
    protected $_processedRowsCount = 0;

    /**
     * @var array
     */
    protected $_defaultZoneValues;

    /**
     * @var DirectoryList
     */
    protected $_directoryList;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @var \Digidirect\ExtendedShippingRates\Model\ZoneFactory
     */
    protected $zoneFactory;

    /**
     * @var array
     */
    protected $_requiredFields = [
        ZoneInterface::NAME,
        self::ZONE_ID_COLUMN_HEAD,
        self::STATE_COLUMN_HEAD
    ];

    /**
     * @var array
     */
    protected $_columnMapping = [
        self::ZONE_ID_COLUMN_HEAD => ZoneInterface::ZONE_ID,
        self::STATE_COLUMN_HEAD => ZoneInterface::REGION,
        self::COUNTRY_COLUMN_HEAD => ZoneInterface::COUNTRY_ID,
        self::ACTIVE_COLUMN_HEAD => ZoneInterface::IS_ACTIVE
    ];

    /**
     * @var array
     */
    protected $_activeValueMapping = [
        'y' => 1,
        'n' => 0
    ];

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * @var \Magento\Framework\Message\Manager
     */
    protected $messageManager;

    /**
     * Import constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Digidirect\ExtendedShippingRates\Model\ResourceModel\Zone\Import\Data $importData
     * @param \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errorAggregator
     * @param DirectoryList $directoryList
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Digidirect\ExtendedShippingRates\Model\ZoneFactory $zoneFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Framework\Message\Manager $manager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $defaultZoneValues
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Digidirect\ExtendedShippingRates\Model\ResourceModel\Zone\Import\Data $importData,
        \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Digidirect\ExtendedShippingRates\Model\ZoneFactory $zoneFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Message\Manager $manager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $defaultZoneValues = [],
        array $data = []
    ) {
        $this->_jsonHelper = $jsonHelper;
        $this->_resourceHelper = $resourceHelper;
        $this->_importExportData = $importExportData;
        $this->_dataSourceModel = $importData;
        $this->_errorAggregator = $errorAggregator;
        $this->_filesystem = $filesystem;
        $this->_defaultZoneValues = $defaultZoneValues;
        $this->_directoryList = $directoryList;
        $this->_directoryHelper = $directoryHelper;
        $this->zoneFactory = $zoneFactory;
        $this->serializer = $serializer;
        $this->regionFactory = $regionFactory;
        $this->countryFactory = $countryFactory;
        $this->messageManager = $manager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Validate data rows and save bunches to DB.
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _saveValidatedBunches()
    {
        $source = $this->_getSource();
        $currentDataSize = 0;
        $bunchRows = [];
        $startNewBunch = false;
        $nextRowBackup = [];
        $maxDataSize = $this->_resourceHelper->getMaxDataSize();
        $bunchSize = $this->_importExportData->getBunchSize();

        $allBunches = [];
        $bunchNum = 0;

        $source->rewind();

        while ($source->valid() || $bunchRows) {
            if ($startNewBunch || !$source->valid()) {
                $bunchRows = $nextRowBackup;
                $currentDataSize = strlen(serialize($bunchRows));
                $startNewBunch = false;
                $nextRowBackup = [];
            }
            if ($source->valid()) {
                try {
                    $rowData = $source->current();
                } catch (\InvalidArgumentException $e) {
                    $this->addRowError($e->getMessage(), $this->_processedRowsCount);
                    $this->_processedRowsCount++;
                    $source->next();
                    continue;
                }

                $this->_processedRowsCount++;

                if ($this->validateRow($rowData, $source->key())) {
                    // add row to bunch for save
                    $rowData = $this->_prepareRowForDb($rowData);
                    $rowSize = strlen($this->_jsonHelper->jsonEncode($rowData));

                    $isBunchSizeExceeded = $bunchSize > 0 && count($bunchRows) >= $bunchSize;

                    if ($currentDataSize + $rowSize >= $maxDataSize || $isBunchSizeExceeded) {
                        $startNewBunch = true;
                        $nextRowBackup = [$source->key() => $rowData];
                        $bunchNum++;
                    } else {
                        $bunchRows[$source->key()] = $rowData;
                        $currentDataSize += $rowSize;
                        $allBunches[$bunchNum] = $bunchRows;
                    }
                }
                $source->next();
            }
        }

        $this->validateImportProcess();
        if (!empty($allBunches)) {
            $uniqueRows = $this->_groupRowData($allBunches);
            $bunches = array_chunk($this->_prepareRowConditionsForDb($uniqueRows), $bunchSize, true);
            foreach ($bunches as $bunch) {
                $this->_dataSourceModel->saveBunch($bunch);
                $entity = $this->_dataSourceModel->getSavedRowIds(array_keys($bunch));
                foreach ($entity as $key => $value) {
                    $entity[$key]['store_id'] = Store::DEFAULT_STORE_ID;
                }

                $this->_dataSourceModel->saveDefaultStore($entity);
            }
        }

        $this->getErrorAggregator()->getErrorsCount();

        return $this;
    }

    /**
     * @param array $allBunches
     * @return array
     */
    protected function _groupRowData($allBunches)
    {
        $uniqueRowsData = [];
        foreach ($allBunches as $bunch) {
            foreach ($bunch as $rowData) {
                /** @var array $rowData */
                if (empty($uniqueRowsData[$rowData[ZoneInterface::ZONE_ID]])) {
                    $uniqueRowsData[$rowData[ZoneInterface::ZONE_ID]] = $rowData;
                } else {
                    $zone = $uniqueRowsData[$rowData[ZoneInterface::ZONE_ID]];
                    $postcode = array_merge(
                        explode(',', $zone[ZoneInterface::POSTCODE]),
                        explode(',', $rowData[ZoneInterface::POSTCODE])
                    );

                    $uniqueRowsData[$rowData[ZoneInterface::ZONE_ID]][ZoneInterface::POSTCODE] =
                        implode(',', array_unique($postcode));
                }
            }
        }

        return $uniqueRowsData;
    }

    /**
     * @param array $uniqueRowsData
     * @return array
     */
    protected function _prepareRowConditionsForDb($uniqueRowsData)
    {
        if (!empty($uniqueRowsData)) {
            foreach ($uniqueRowsData as $id => $row) {
                /** @var $model \Digidirect\ExtendedShippingRates\Model\Zone */
                $model = $this->zoneFactory->create();
                $model->createSimpleConditions($row);
                $model->loadPost($row);
                // Serialize conditions
                if ($model->getConditions()) {
                    $model->setConditionsSerialized($this->serializer->serialize($model->getConditions()->asArray()));
                }
                $uniqueRowsData[$id] = $model->getData();
            }
        }

        return $uniqueRowsData;
    }

    /**
     * @return \Magento\ImportExport\Model\Import\Source\Csv
     */
    protected function _getSource()
    {
        return $this->_source;
    }

    /**
     * @param \Magento\ImportExport\Model\Import\Source\Csv $source
     * @return $this
     */
    protected function _setSource(\Magento\ImportExport\Model\Import\Source\Csv $source)
    {
        $this->_source = $source;

        return $this;
    }

    /**
     * Process zone import
     *
     * @param array $file
     * @return $this
     */
    public function processImport($file)
    {
        /**
         * @var \Magento\ImportExport\Model\Import\Source\Csv $source
         */
        $source = ImportAdapter::findAdapterFor(
            $this->_directoryList->getPath('tmp') . DIRECTORY_SEPARATOR . $file['name'],
            $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT)
        );
        $this->_setSource($source);
        $this->_saveValidatedBunches();

        return $this;
    }

    /**
     * Add error with corresponding current data source row number.
     *
     * @param string $errorCode Error code or simply column name
     * @param int $errorRowNum Row number.
     * @param string $colName OPTIONAL Column name.
     * @param string $errorMessage OPTIONAL Column name.
     * @param string $errorLevel
     * @param string $errorDescription
     * @return $this
     */
    public function addRowError(
        $errorCode,
        $errorRowNum,
        $colName = null,
        $errorMessage = null,
        $errorLevel = ProcessingError::ERROR_LEVEL_CRITICAL,
        $errorDescription = null
    ) {
        $errorCode = (string)$errorCode;
        $this->getErrorAggregator()->addError(
            $errorCode,
            $errorLevel,
            $errorRowNum,
            $colName,
            $errorMessage,
            $errorDescription
        );

        return $this;
    }

    /**
     * @return \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface
     */
    public function getErrorAggregator()
    {
        return $this->_errorAggregator;
    }

    /**
     * Check exist errors while importing
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateImportProcess()
    {
        if ($this->getErrorAggregator()->getErrorsCount()) {
            foreach ($this->getErrorAggregator()->getRowsGroupedByErrorCode() as $errorCode => $rows) {
                $error = $errorCode . ' ' . __('on line(s):') . ' ' . implode(', ', $rows);
                $this->messageManager->addErrorMessage($error);
            }
            throw new \Magento\Framework\Exception\LocalizedException(__('Incorrect data.'));
        }
    }

    /**
     * Change row data before saving in DB table.
     *
     * @param array $rowData
     * @return array
     */
    protected function _prepareRowForDb(array $rowData)
    {
        /**
         * Convert all empty strings to null values, as
         * a) we don't use empty string in DB
         * b) empty strings instead of numeric values will product errors in Sql Server
         */
        foreach ($rowData as $key => $val) {
            if (array_key_exists($key, $this->_columnMapping)) {
                $value = ($val === '') ? null : $val;
                $rowData[$this->_columnMapping[$key]] = $value;
                unset($rowData[$key]);
            } else {
                if ($val === '') {
                    $rowData[$key] = null;
                }
            }
        }

        foreach ($this->_defaultZoneValues as $key => $val) {
            if (!isset($rowData[$key]) || $rowData[$key] === null) {
                $rowData[$key] = $val;
            }
        }

        $this->_prepareStatusForDb($rowData);
        $this->_prepareCountryDataForDb($rowData);
        $this->_prepareRegionDataForDb($rowData);

        return $rowData;
    }

    /**
     * @param array $rowData
     * @return void
     */
    public function _prepareStatusForDb(&$rowData)
    {
        if (!empty($rowData[ZoneInterface::IS_ACTIVE])) {
            $key = strtolower($rowData[ZoneInterface::IS_ACTIVE]);
            $value = !empty($this->_activeValueMapping[$key])
                ? $this->_activeValueMapping[$key]
                : $this->_defaultZoneValues[ZoneInterface::IS_ACTIVE];
            $rowData[ZoneInterface::IS_ACTIVE] = $value;
        }
    }

    /**
     * @param array $rowData
     * @return void
     */
    public function _prepareCountryDataForDb(&$rowData)
    {
        if (empty($rowData[ZoneInterface::COUNTRY_ID])) {
            $rowData[ZoneInterface::COUNTRY_ID] = $this->_directoryHelper->getDefaultCountry();
        } else {
            $country = $this->countryFactory->create()->loadByCode($rowData[ZoneInterface::COUNTRY_ID]);
            $rowData[ZoneInterface::COUNTRY_ID] = $country->getCountryId();
        }
    }

    /**
     * @param array $rowData
     * @return void
     */
    public function _prepareRegionDataForDb(&$rowData)
    {
        if (!empty($rowData[ZoneInterface::REGION])
            && strtoupper($rowData[ZoneInterface::REGION]) != self::ALL_STATE_FLAG
        ) {
            $region = $this->regionFactory->create()->loadByCode(
                $rowData[ZoneInterface::REGION],
                $rowData[ZoneInterface::COUNTRY_ID]
            );
            if (!$region->getId()) {
                $field = array_flip($this->_columnMapping)[ZoneInterface::REGION];
                $rowNum = $this->_getSource()->key() + 1;
                $this->addRowError(
                    $field . static::ERROR_VALUE_IS_MANDATORY,
                    $rowNum,
                    strtoupper($field),
                    "Column '%s' contains wrong value"
                );
            }
            $rowData[ZoneInterface::REGION_ID] = $region->getId();
            unset($rowData[ZoneInterface::REGION]);
        } else {
            unset($rowData[ZoneInterface::REGION]);
            $rowData[ZoneInterface::REGION_ID] = null;
        }
    }

    /**
     * Validate data row
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        $error = false;
        foreach ($this->_requiredFields as $field) {
            if (empty($rowData[$field])) {
                $this->addRowError(
                    $field . static::ERROR_VALUE_IS_MANDATORY,
                    $rowNum + 1,
                    strtoupper($field),
                    "Mandatory column '%s' has no value"
                );
                $error = true;
            }
        }

        return $error ? false : true;
    }

    /**
     * Remove file
     *
     * @param array $file
     * @return $this
     */
    public function removeCsvFiles(array $file)
    {
        $directory = $this->_filesystem->getDirectoryWrite(DirectoryList::TMP);
        $directory->delete($file['name']);

        return $this;
    }
}
