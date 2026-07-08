<?php
namespace Digidirect\ExtendedShippingRates\Model\Rate;

use Magento\ImportExport\Model\Import\Adapter as ImportAdapter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;

/**
 * Class Import
 * @package Digidirect\ExtendedShippingRates\Model\Rate
 */
class Import extends \Magento\Framework\Model\AbstractModel
{
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
    protected $_defaultRateValues;

    /**
     * Import constructor.
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Digidirect\ExtendedShippingRates\Model\ResourceModel\Rate\Import\Data $importData
     * @param \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errorAggregator
     * @param array $defaultRateValues
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Digidirect\ExtendedShippingRates\Model\ResourceModel\Rate\Import\Data $importData,
        \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errorAggregator,
        array $defaultRateValues
    ) {
        $this->_defaultRateValues = $defaultRateValues;
        $this->_jsonHelper = $jsonHelper;
        $this->_resourceHelper = $resourceHelper;
        $this->_importExportData = $importExportData;
        $this->_dataSourceModel = $importData;
        $this->_errorAggregator = $errorAggregator;
        $this->_filesystem = $filesystem;
    }

    /**
     * Validate data rows and save bunches to DB.
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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

        $source->rewind();

        while ($source->valid() || $bunchRows) {
            if ($startNewBunch || !$source->valid()) {
                $this->validateImportProcess();
                $this->_dataSourceModel->saveBunch($bunchRows);

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
                    } else {
                        $bunchRows[$source->key()] = $rowData;
                        $currentDataSize += $rowSize;
                    }
                }
                $source->next();
            }
        }
        $this->getErrorAggregator()->getErrorsCount();
        return $this;
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
     * Process rates import
     *
     * @param array $rateFile
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processImport($rateFile)
    {
        if ($this->getMethodId() === null) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('There is no shipping method id.')
            );
        }
        /**
         * @var \Magento\ImportExport\Model\Import\Source\Csv $source
         */
        $source = ImportAdapter::findAdapterFor(
            $rateFile['path'] . DIRECTORY_SEPARATOR . $rateFile['name'],
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function validateImportProcess()
    {
        $errors = [];
        if ($this->getErrorAggregator()->getErrorsCount()) {
            foreach ($this->getErrorAggregator()->getAllErrors() as $error) {
                $errors[] = $error->getErrorMessage();
            }
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase(implode('\\n', $errors))
            );
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
            if ($val === '') {
                $rowData[$key] = null;
            }
        }
        foreach ($this->_defaultRateValues as $key => $val) {
            if (!isset($rowData[$key]) || $rowData[$key] === null) {
                $rowData[$key] = $val;
            }
        }
        $rowData['method_id'] = $this->getMethodId();
        return $rowData;
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
        return true;
    }

    /**
     * Remove files
     *
     * @param array $files
     * @return $this
     */
    public function removeCsvFiles(array $files)
    {
        $directory = $this->_filesystem->getDirectoryWrite(DirectoryList::TMP);
        foreach ($files as $file) {
            $directory->delete($file['name']);
        }
        return $this;
    }
}
