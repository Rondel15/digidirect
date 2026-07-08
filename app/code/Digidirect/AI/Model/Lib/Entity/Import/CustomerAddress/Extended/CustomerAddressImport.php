<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\CustomerAddress\Extended;

use Magento\CustomerImportExport\Model\Import\Address as MagentoCustomerAddressImport;
use Magento\ImportExport\Model\Import as MagentoImport;
use Digidirect\AI\Model\Lib\Entity\Import\Service\ErrorProcessing\ProcessingErrorAggregator as AIErrorAggregator;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\ResourceModel\Helper;
use \Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\ImportExport\Model\Export\Factory;
use Magento\Eav\Model\Config;
use Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\StorageFactory;
use Magento\Customer\Model\AddressFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory as AddressCollectionFactory;
use Magento\Customer\Model\ResourceModel\Address\Attribute\CollectionFactory as AddressAttributesCollectionFactory;
use Magento\Framework\Stdlib\DateTime;
use Magento\Customer\Model\Address\Validator\Postcode;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Class CustomerImport
 *
 * @package Digidirect\AI\Model\Lib\Entity\Import\Customer\Extended
 * @method AIErrorAggregator getErrorAggregator
 */
class CustomerAddressImport extends MagentoCustomerAddressImport
{
    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var array
     */
    protected $excludedAddressIds = [];

    /**
     * CustomerAddressImport constructor.
     *
     * @param StringUtils $string
     * @param ScopeConfigInterface $scopeConfig
     * @param ImportFactory $importFactory
     * @param Helper $resourceHelper
     * @param ResourceConnection $resource
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param StoreManagerInterface $storeManager
     * @param Factory $collectionFactory
     * @param Config $eavConfig
     * @param StorageFactory $storageFactory
     * @param AddressFactory $addressFactory
     * @param RegionCollectionFactory $regionColFactory
     * @param CustomerFactory $customerFactory
     * @param AddressCollectionFactory $addressColFactory
     * @param AddressAttributesCollectionFactory $attributesFactory
     * @param DateTime $dateTime
     * @param Postcode $postcodeValidator
     * @param array $data
     * @param array $configuration
     */
    public function __construct(
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        ImportFactory $importFactory,
        Helper $resourceHelper,
        ResourceConnection $resource,
        ProcessingErrorAggregatorInterface $errorAggregator,
        StoreManagerInterface $storeManager,
        Factory $collectionFactory,
        Config $eavConfig,
        StorageFactory $storageFactory,
        AddressFactory $addressFactory,
        RegionCollectionFactory $regionColFactory,
        CustomerFactory $customerFactory,
        AddressCollectionFactory $addressColFactory,
        AddressAttributesCollectionFactory $attributesFactory,
        DateTime $dateTime,
        Postcode $postcodeValidator,
        array $data = [],
        array $configuration = []
    ) {
        $this->configuration = $configuration;
        if (property_exists($this, '_addressCollection')) {
            parent::__construct( //compatibility with versions < 2.3
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
                $addressFactory,
                $regionColFactory,
                $customerFactory,
                $addressColFactory,
                $attributesFactory,
                $dateTime,
                $postcodeValidator,
                $data
            );
        } else {
            parent::__construct( //from 2.3 $addressColFactory was removed from the constructor
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
                $addressFactory,
                $regionColFactory,
                $customerFactory,
                $attributesFactory,
                $dateTime,
                $postcodeValidator,
                $data
            );
        }

        array_push(
            $this->_availableBehaviors,
            MagentoImport::BEHAVIOR_APPEND,
            MagentoImport::BEHAVIOR_REPLACE
        );
    }

    /**
     * Import data rows
     *
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @codeCoverageIgnore
     */
    protected function _importData()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $newRows = [];
            $updateRows = [];
            $attributes = [];
            $defaults = [];
            // customer default addresses (billing/shipping) data
            $deleteRowIds = [];

            foreach ($bunch as $rowNumber => $rowData) {
                // check row data
                if ($this->_isOptionalAddressEmpty($rowData) || !$this->validateRow($rowData, $rowNumber)) {
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }

                if ($this->getBehavior($rowData) == MagentoImport::BEHAVIOR_DELETE) {
                    $deleteRowIds[] = $rowData[self::COLUMN_ADDRESS_ID];
                } else {
                    $addUpdateResult = $this->_prepareDataForUpdate($rowData);
                    if ($addUpdateResult['entity_row_new']) {
                        $newRows[] = $addUpdateResult['entity_row_new'];
                        $newRows = $this->getDataForAction('create', $newRows);
                    }
                    if ($addUpdateResult['entity_row_update']) {
                        $updateRows[] = $addUpdateResult['entity_row_update'];
                        $updateRows = $this->getDataForAction('update', $updateRows);
                    }
                    $attributes = $this->_mergeEntityAttributes($addUpdateResult['attributes'], $attributes);
                    $attributes = $this->prepareAttributes($attributes);
                    $defaults = $this->_mergeEntityAttributes($addUpdateResult['defaults'], $defaults);
                }
            }

            $this->updateItemsCounterStats($newRows, $updateRows, $deleteRowIds);

            $this->_saveAddressEntities(
                $newRows,
                $updateRows
            )->_saveAddressAttributes(
                $attributes
            )->_saveCustomerDefaults(
                $defaults
            );

            $this->_deleteAddressEntities($deleteRowIds);
        }
        return true;
    }

    /**
     * @param array $attributes
     * @return array
     */
    protected function prepareAttributes(array $attributes = [])
    {
        if (empty($attributes)) {
            return $attributes;
        }

        foreach ($attributes as $tableName => $attributeConfig) {
            foreach ($attributeConfig as $addressId => $valuesToInsert) {
                if (in_array($addressId, $this->excludedAddressIds)) {
                    unset($attributes[$tableName][$addressId]);
                    if (empty($attributes[$tableName])) {
                        unset($attributes[$tableName]);
                    }
                }
            }
        }

        return $attributes;
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
    protected function getDataForAction($action, $dataForAction)
    {
        $behaviour = $this->getBehavior();
        $emptyBehavioursForAction = $this->configuration['actions'][$action]['empty'] ?? [];
        $data = in_array($behaviour, $emptyBehavioursForAction) ? [] : $dataForAction;

        if (empty($data)) {
            foreach ($dataForAction as $addressData) {
                $addressId = $addressData['entity_id'] ?? null;
                if ($addressId) {
                    $this->excludedAddressIds[] = $addressId;
                }
            }
        }
        return $data;
    }
}
