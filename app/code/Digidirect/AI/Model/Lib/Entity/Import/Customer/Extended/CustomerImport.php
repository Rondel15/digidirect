<?php

namespace Digidirect\AI\Model\Lib\Entity\Import\Customer\Extended;

use Magento\CustomerImportExport\Model\Import\Customer as MagentoCustomerImport;
use Magento\ImportExport\Model\Import as MagentoImport;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Class CustomerImport
 *
 * @package Digidirect\AI\Model\Lib\Entity\Import\Customer\Extended
 * @method \Digidirect\AI\Model\Lib\Entity\Import\Service\ErrorProcessing\ProcessingErrorAggregator getErrorAggregator
 */
class CustomerImport extends MagentoCustomerImport
{
    const MULTISELECT_ATTRIBUTE = 'multiselect';

    /**
     * @var array
     */
    protected $multipleSelectAttributes = [];

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var array
     */
    protected $excludedCustomerIds = [];

    /**
     * CustomerImport constructor.
     *
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\ImportExport\Model\ImportFactory $importFactory
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ImportExport\Model\Export\Factory $collectionFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\StorageFactory $storageFactory
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attrCollectionFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param array $data
     * @param array $configuration
     */
    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\ImportExport\Model\ImportFactory $importFactory,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\StorageFactory $storageFactory,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attrCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        array $data = [],
        array $configuration = []
    ) {
        $this->configuration = $configuration;
        parent::__construct(
            $string,
            $scopeConfig,
            $importFactory,
            $resourceHelper,
            $resource,
            $errorAggregator,
            $storeManager,
            $collectionFactory,
            $eavConfig,
            $storageFactory,
            $attrCollectionFactory,
            $customerFactory,
            $data
        );

        array_push(
            $this->_availableBehaviors,
            MagentoImport::BEHAVIOR_APPEND,
            MagentoImport::BEHAVIOR_REPLACE
        );
    }

    /**
     * Overwritten method.
     * 1) Removed "delete" functionality
     * 2) if $updateOnDuplicate = false - set $entitiesToUpdate variable to empty array
     *
     * @return bool
     * @codeCoverageIgnore
     */
    protected function _importData()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entitiesToCreate = [];
            $entitiesToUpdate = [];
            $entitiesToDelete = [];
            $attributesToSave = [];

            foreach ($bunch as $rowNumber => $rowData) {
                if (!$this->validateRow($rowData, $rowNumber)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }

                if ($this->getBehavior($rowData) == MagentoImport::BEHAVIOR_DELETE) {
                    $entitiesToDelete[] = $this->_getCustomerId(
                        $rowData[self::COLUMN_EMAIL],
                        $rowData[self::COLUMN_WEBSITE]
                    );

                    $entitiesToDelete = $this->getDataForAction('delete', $entitiesToDelete);
                } else {
                    $processedData = $this->_prepareDataForUpdate($rowData);
                    $entitiesToCreate = array_merge($entitiesToCreate, $processedData[self::ENTITIES_TO_CREATE_KEY]);
                    $entitiesToCreate = $this->getDataForAction('create', $entitiesToCreate);

                    $entitiesToUpdate = array_merge($entitiesToUpdate, $processedData[self::ENTITIES_TO_UPDATE_KEY]);
                    $entitiesToUpdate = $this->getDataForAction('update', $entitiesToUpdate);

                    foreach ($processedData[self::ATTRIBUTES_TO_SAVE_KEY] as $tableName => $customerAttributes) {
                        $attributesToSave[$tableName] = $attributesToSave[$tableName] ?? [];
                        $attributesToSave[$tableName] = array_diff_key(
                            $attributesToSave[$tableName],
                            $customerAttributes
                        ) + $customerAttributes;
                    }
                }
            }
            $this->updateItemsCounterStats($entitiesToCreate, $entitiesToUpdate, $entitiesToDelete);

            /**
             * Save prepared data
             */
            if ($entitiesToCreate || $entitiesToUpdate) {
                $this->_saveCustomerEntities($entitiesToCreate, $entitiesToUpdate);
            }
            if ($attributesToSave) {
                $attributesToSave = $this->prepareAttributesToSave($attributesToSave);
                $this->_saveCustomerAttributes($attributesToSave);
            }

            if ($entitiesToDelete) {
                $this->_deleteCustomerEntities($entitiesToDelete);
            }
        }

        $this->excludedCustomerIds = [];
        return true;
    }

    /**
     * @param [] $attributesToSave
     * @return []
     */
    protected function prepareAttributesToSave(array $attributesToSave)
    {
        if (!empty($this->excludedCustomerIds) && !empty($attributesToSave)) {
            foreach ($attributesToSave as $table => $customerAttributeData) {
                foreach ($customerAttributeData as $customerId => $valuesToInsert) {
                    if (in_array($customerId, $this->excludedCustomerIds)) {
                        unset($attributesToSave[$table][$customerId]);
                        if (empty($attributesToSave[$table])) {
                            unset($attributesToSave[$table]);
                        }
                    }
                }
            }
        }

        return $attributesToSave;
    }

    /**
     * Get data depends on action and import behaviour
     *
     * For example:
     *     Behaviour "replace": $entitiesToCreate = [], $entitiesToUpdate = $dataForAction
     *     Behaviour "append": $entitiesToCreate = $dataForAction, $entitiesForUpdate = []
     *     Behaviour "add_update": $entitiesToCreate = $dataForAction, $entitiesToUpdate = $dataForAction
     *
     * It can be configured in di.xml as constructor argument  "configuration"
     *
     * @param string $action
     * @param [] $dataForAction
     * @return array
     */
    protected function getDataForAction($action, array $dataForAction)
    {
        $behaviour = $this->getBehavior();
        $emptyBehavioursForAction = $this->configuration['actions'][$action]['empty'] ?? [];
        $data = in_array($behaviour, $emptyBehavioursForAction) ? [] : $dataForAction;

        if (empty($data)) {
            foreach ($dataForAction as $customerData) {
                $customerEntityId = $customerData['entity_id'] ?? null;
                if ($customerEntityId) {
                    $this->excludedCustomerIds[] = $customerEntityId;
                }
            }
        }
        return $data;
    }

    /**
     *
     * Overwritten due incorrect multiselect processing
     *
     * @param string $attributeCode
     * @param array $attributeParams
     * @param array $rowData
     * @param int $rowNumber
     * @param string $multiSeparator
     * @return bool
     */
    public function isAttributeValid(
        $attributeCode,
        array $attributeParams,
        array $rowData,
        $rowNumber,
        $multiSeparator = \Magento\ImportExport\Model\Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR
    ) {
        $type = $attributeParams['type'] ?? null;
        if ($type != self::MULTISELECT_ATTRIBUTE) {
            // @codeCoverageIgnoreStart
            $valid = parent::isAttributeValid($attributeCode, $attributeParams, $rowData, $rowNumber, $multiSeparator);
            return $valid;
            // @codeCoverageIgnoreEnd
        }

        $options = $attributeParams['options'] ?? [];
        $attributeValue = $rowData[$attributeCode];

        $attributeValueAsArray = explode(',', $attributeValue);

        $valid = false;
        foreach ($attributeValueAsArray as $value) {
            $value = strtolower($value);
            if (in_array($value, $options) || isset($options[$value])) {
                $valid = true;
                break;
            }
        }
        return $valid;
    }

    /**
     * Converts multiple select text values to magento ids
     *
     * It also check if input data already contains magento ids
     *
     * @param string $attributeCode
     * @param string $attributeValue
     * @param [] $rowData
     * @return void
     */
    protected function convertMultiselect($attributeCode, $attributeValue, &$rowData)
    {
        $attributeConfig = $this->multipleSelectAttributes[$attributeCode];
        $availableOptions = $attributeConfig['options'] ?? [];
        $givenOptions = explode(',', $attributeValue);

        $newOptions = [];
        foreach ($givenOptions as $givenOption) {
            $givenOption = strtolower(trim($givenOption));
            if (in_array($givenOption, $availableOptions)) {
                $newOptions[] = $givenOption;
            }

            if (isset($availableOptions[$givenOption])) {
                $newOptions[] = $availableOptions[$givenOption];
            }
        }

        $newOptions = implode(',', $newOptions);
        $rowData[$attributeCode] = $newOptions;
    }

    /**
     * Change row data before preparing to save - change text options for multiple select attribute type to ids
     *
     * @codeCoverageIgnore
     * @param array $rowData
     * @return array
     */
    protected function _prepareDataForUpdate(array $rowData)
    {
        if (empty($this->multipleSelectAttributes)) {
            foreach ($this->_attributes as $attributeCode => $attributeConfig) {
                $type = $attributeConfig['type'] ?? null;
                if ($type == self::MULTISELECT_ATTRIBUTE) {
                    $this->multipleSelectAttributes[$attributeCode] = $attributeConfig;
                }
            }
        }

        foreach (array_intersect_key($rowData, $this->_attributes) as $attributeCode => $attributeValue) {
            if ($this->isMultipleSelect($attributeCode)) {
                $this->convertMultiselect($attributeCode, $attributeValue, $rowData);
            }
        }

        return parent::_prepareDataForUpdate($rowData);
    }

    /**
     * @param string $attributeCode
     * @return bool
     */
    protected function isMultipleSelect($attributeCode)
    {
        return isset($this->multipleSelectAttributes[$attributeCode]);
    }

    /**
     * Update and insert data in entity table
     *
     * @param array $entitiesToCreate Rows for insert
     * @param array $entitiesToUpdate Rows for update
     * @return $this
     */
    protected function _saveCustomerEntities(array $entitiesToCreate, array $entitiesToUpdate)
    {
        if ($entitiesToCreate) {
            $this->_connection->insertMultiple($this->_entityTable, $entitiesToCreate);
        }

        if ($entitiesToUpdate) {
            $this->_connection->insertOnDuplicate(
                $this->_entityTable,
                $entitiesToUpdate
            );
        }

        return $this;
    }
}
