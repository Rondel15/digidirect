<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\Customer;

use Digidirect\AI\Model\Lib\Entity\Import\ImportAbstract;
use Digidirect\AI\Model\Logger\LoggerInterface;
use Digidirect\AI\Model\Lib\Validator\Validate as Validator;
use Digidirect\AI\Model\Lib\Entity\Import\Customer\Extended\CustomerImport;
use Digidirect\AI\Model\Lib\Entity\Import\Customer\Extended\CustomerImportFactory;
use Digidirect\AI\Model\Lib\Entity\Import\Service\Source\ArraySource;
use Digidirect\AI\Model\Lib\Entity\Import\Service\Source\ArraySourceFactory;
use Magento\ImportExport\Model\Import as MagentoImport;
use Magento\Framework\Data\DataArray;
use Digidirect\AI\Model\Lib\Entity\Import\Service\ErrorProcessing\ProcessingErrorAggregator;

/**
 * Class Customer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 */
class Customer extends ImportAbstract implements CustomerInterface
{
    const ENTITY_NAME = 'customer';

    /***
     * @var CustomerImport
     */
    protected $extendedCustomerImport;

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
     * Customer constructor.
     *
     * @param Validator $beforeSaveValidator
     * @param Validator $beforeUpdateValidator
     * @param LoggerInterface $logger
     * @param CustomerImportFactory $customerImportFactory
     * @param ArraySourceFactory $arraySourceFactory
     * @param ProcessingErrorAggregator $errorAggregator
     * @param DataArray $colNames
     * @param array $preparers
     */
    public function __construct(
        Validator $beforeSaveValidator,
        Validator $beforeUpdateValidator,
        LoggerInterface $logger,
        CustomerImportFactory $customerImportFactory,
        ArraySourceFactory $arraySourceFactory,
        ProcessingErrorAggregator $errorAggregator,
        DataArray $colNames,
        array $preparers = []
    ) {
        $this->errorAggregator = $errorAggregator;
        $this->extendedCustomerImport = $customerImportFactory->create();
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
     * @param array $customers
     * @return bool
     */
    protected function _updateBunch(array $customers)
    {
        $arraySource = $this->arraySourceFactory->create([
            'colNames' => $this->colNames,
            'data' => $customers,
        ]);
        return $this->_importBunch($arraySource, MagentoImport::BEHAVIOR_REPLACE);
    }

    /**
     * @return MagentoImport\ErrorProcessing\ProcessingErrorAggregatorInterface
     */
    protected function getErrorAggregator()
    {
        return $this->extendedCustomerImport->getErrorAggregator();
    }

    /**
     * @param ArraySource $arraySource
     * @param string $behavior
     * @return bool
     */
    protected function _importBunch(ArraySource $arraySource, $behavior)
    {
        $this->getErrorAggregator()->clear();
        $this->setParameter('behavior', $behavior);
        $this->extendedCustomerImport->setParameters($this->importParameters);
        $this->extendedCustomerImport->setSource($arraySource);
        $this->extendedCustomerImport->validateData();

        if ($this->getErrorAggregator()->getErrorsCount() > 0) {
            $this->_logger->critical(__('Data is not valid.'), [
                'entity' => $this->getName(),
                'behavior' => $behavior,
                'errors' => $this->getErrorAggregator()->getErrorMessages(),
                'data' => $arraySource->getData(),
            ]);
            return false;
        } else {
            $this->extendedCustomerImport->importData();
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
     * @return bool
     */
    public function deleteCustomer($customerEmail, $websiteCode)
    {
        $customersData = [
            [
                CustomerImport::COLUMN_EMAIL => $customerEmail,
                CustomerImport::COLUMN_WEBSITE => $websiteCode,
            ],
        ];

        return $this->deleteBunch($customersData);
    }

    /**
     * Delete bunch of customers
     *
     * [
     *     [
     *         '_website' => 'Website Code',
     *         'email' => 'Customer email 1'
     *     ],
     *     [
     *         '_website' => 'Website Code',
     *         'email' => 'Customer email 1'
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
            if (!isset($customerInfo[CustomerImport::COLUMN_WEBSITE])) {
                $this->errorAggregator->addError(CustomerImport::ERROR_WEBSITE_IS_EMPTY);
                continue;
            }

            if (!isset($customerInfo[CustomerImport::COLUMN_EMAIL])) {
                $this->errorAggregator->addError(CustomerImport::ERROR_EMAIL_IS_EMPTY);
            }
        }

        $arraySource = $this->arraySourceFactory->create([
            'colNames' => [CustomerImport::COLUMN_EMAIL, CustomerImport::COLUMN_WEBSITE],
            'data' => $customer,
        ]);

        return $this->_importBunch($arraySource, MagentoImport::BEHAVIOR_DELETE);
    }
}
