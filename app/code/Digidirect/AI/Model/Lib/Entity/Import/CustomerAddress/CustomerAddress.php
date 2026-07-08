<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\CustomerAddress;

use Digidirect\AI\Model\Lib\Entity\Import\ImportAbstract;
use Digidirect\AI\Model\Logger\LoggerInterface;
use Digidirect\AI\Model\Lib\Validator\Validate as Validator;
use Digidirect\AI\Model\Lib\Entity\Import\CustomerAddress\Extended\CustomerAddressImport;
use Digidirect\AI\Model\Lib\Entity\Import\CustomerAddress\Extended\CustomerAddressImportFactory;
use Digidirect\AI\Model\Lib\Entity\Import\Service\Source\ArraySource;
use Digidirect\AI\Model\Lib\Entity\Import\Service\Source\ArraySourceFactory;
use Magento\ImportExport\Model\Import as MagentoImport;
use Magento\Framework\Data\DataArray;
use Digidirect\AI\Model\Lib\Entity\Import\Service\ErrorProcessing\ProcessingErrorAggregator;

/**
 * Class CustomerAddress
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 */
class CustomerAddress extends ImportAbstract implements CustomerAddressInterface
{
    const ENTITY_NAME = 'customer_address';

    /***
     * @var CustomerAddressImport
     */
    protected $extendedCustomerAddressImport;

    /**
     * @var ArraySourceFactory
     */
    protected $arraySourceFactory;

    /**
     * @var ProcessingErrorAggregator
     */
    protected $errorAggregator;

    /**
     * @var array
     */
    protected $importParameters = [];

    /**
     * @var array
     */
    protected $colNames;

    /**
     * CustomerAddress constructor.
     *
     * @param Validator $beforeSaveValidator
     * @param Validator $beforeUpdateValidator
     * @param LoggerInterface $logger
     * @param CustomerAddressImportFactory $customerAddressImportFactory
     * @param ArraySourceFactory $arraySourceFactory
     * @param ProcessingErrorAggregator $errorAggregator
     * @param DataArray $colNames
     * @param array $preparers
     */
    public function __construct(
        Validator $beforeSaveValidator,
        Validator $beforeUpdateValidator,
        LoggerInterface $logger,
        CustomerAddressImportFactory $customerAddressImportFactory,
        ArraySourceFactory $arraySourceFactory,
        ProcessingErrorAggregator $errorAggregator,
        DataArray $colNames,
        array $preparers = []
    ) {
        $this->errorAggregator = $errorAggregator;
        $this->extendedCustomerAddressImport = $customerAddressImportFactory->create();
        $this->arraySourceFactory = $arraySourceFactory;
        $this->colNames = array_keys($colNames->getData());
        parent::__construct($beforeSaveValidator, $beforeUpdateValidator, $logger, $preparers);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::ENTITY_NAME;
    }

    /**
     * @param array $customer
     * @param bool $updateOnDuplicate
     * @return bool
     */
    protected function _save(array $customer, $updateOnDuplicate = true)
    {
        /**
         * @var $arraySource ArraySource
         */
        $arraySource = $this->arraySourceFactory->create(['colNames' => $this->colNames]);
        $arraySource->addEntity($customer);
        $behavior = $updateOnDuplicate ? MagentoImport::BEHAVIOR_ADD_UPDATE : MagentoImport::BEHAVIOR_APPEND;
        return $this->_importBunch($arraySource, $behavior);
    }

    /**
     * Save multiple customers array
     *
     * @param array $customers
     * @param bool $updateOnDuplicate
     * @return bool
     */
    protected function _saveBunch(array $customers, $updateOnDuplicate = true)
    {
        $arraySource = $this->arraySourceFactory->create([
            'colNames' => $this->colNames,
            'data' => $customers,
        ]);
        $behavior = $updateOnDuplicate ? MagentoImport::BEHAVIOR_ADD_UPDATE : MagentoImport::BEHAVIOR_APPEND;
        return $this->_importBunch($arraySource, $behavior);
    }

    /**
     * @param array $customer
     * @return mixed
     */
    protected function _update(array $customer)
    {
        $arraySource = $this->arraySourceFactory->create(['colNames' => $this->colNames]);
        $arraySource->addEntity($customer);
        return $this->_importBunch($arraySource, MagentoImport::BEHAVIOR_REPLACE);
    }

    /**
     * @param array $addresses
     * @return bool
     */
    protected function _updateBunch(array $addresses)
    {
        $arraySource = $this->arraySourceFactory->create([
            'colNames' => $this->colNames,
            'data' => $addresses,
        ]);
        return $this->_importBunch($arraySource, MagentoImport::BEHAVIOR_REPLACE);
    }

    /**
     * @return MagentoImport\ErrorProcessing\ProcessingErrorAggregatorInterface
     */
    protected function getErrorAggregator()
    {
        return $this->extendedCustomerAddressImport->getErrorAggregator();
    }

    /**
     * @param ArraySource $arraySource
     * @param string $behavior
     * @return bool
     */
    protected function _importBunch(ArraySource $arraySource, string $behavior)
    {
        $this->getErrorAggregator()->clear();
        $this->setParameter('behavior', $behavior);
        $this->extendedCustomerAddressImport->setParameters($this->importParameters);
        $this->extendedCustomerAddressImport->setSource($arraySource);
        $this->extendedCustomerAddressImport->validateData();

        if ($this->getErrorAggregator()->getErrorsCount() > 0) {
            $this->_logger->critical(__('Data is not valid.'), [
                'entity' => $this->getName(),
                'behavior' => $behavior,
                'errors' => $this->getErrorAggregator()->getErrorMessages(),
                'data' => $arraySource->getData(),
            ]);
            return false;
        } else {
            $this->extendedCustomerAddressImport->importData();
        }
        return true;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setParameter($name, $value)
    {
        $this->importParameters[$name] = $value;
        return $this;
    }

    /**
     * @param string $customerEmail
     * @param string $websiteCode
     * @param int|string $addressId
     * @return bool
     */
    public function deleteCustomerAddress($customerEmail, $websiteCode, $addressId)
    {
        $customersData = [
            [
                CustomerAddressImport::COLUMN_ADDRESS_ID => $addressId,
                CustomerAddressImport::COLUMN_WEBSITE => $websiteCode,
                CustomerAddressImport::COLUMN_EMAIL => $customerEmail,
            ],
        ];

        return $this->deleteBunch($customersData);
    }

    /**
     * Delete bunch of customer(s) addresses
     *
     * [
     *     [
     *         '_website' => 'Website Code',
     *         '_email' => 'Customer email 1'
     *         '_entity_id' => 'Customer address Entity ID'
     *     ],
     *     [
     *         '_website' => 'Website Code',
     *         '_email' => 'Customer email 1',
     *         '_entity_id' => 'Customer address Entity ID'
     *     ]
     * ]
     *
     *
     * @param array $customer
     * @return bool
     */
    public function deleteBunch(array $customer)
    {
        foreach ($customer as $key => $customerInfo) {
            if (!isset($customerInfo[CustomerAddressImport::COLUMN_ADDRESS_ID])) {
                $this->errorAggregator->addError(CustomerAddressImport::ERROR_ADDRESS_ID_IS_EMPTY);
                continue;
            }

            if (!isset($customerInfo[CustomerAddressImport::COLUMN_WEBSITE])) {
                $this->errorAggregator->addError(CustomerAddressImport::ERROR_WEBSITE_IS_EMPTY);
                continue;
            }

            if (!isset($customerInfo[CustomerAddressImport::COLUMN_EMAIL])) {
                $this->errorAggregator->addError(CustomerAddressImport::ERROR_EMAIL_IS_EMPTY);
                continue;
            }
        }

        $arraySource = $this->arraySourceFactory->create([
            'colNames' => [
                CustomerAddressImport::COLUMN_ADDRESS_ID,
                CustomerAddressImport::COLUMN_WEBSITE,
                CustomerAddressImport::COLUMN_EMAIL,
            ],
            'data' => $customer,
        ]);
        return $this->_importBunch($arraySource, MagentoImport::BEHAVIOR_DELETE);
    }
}
