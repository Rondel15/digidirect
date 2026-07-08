<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\CustomerComposite;

use Digidirect\AI\Model\Lib\Entity\Import\Customer\CustomerInterface;
use Digidirect\AI\Model\Lib\Entity\Import\ImportAbstract;
use Digidirect\AI\Model\Logger\LoggerInterface;
use Digidirect\AI\Model\Lib\Validator\Validate as Validator;
use Digidirect\AI\Model\Lib\Entity\Import\Customer\CustomerInterfaceFactory;
use Digidirect\AI\Model\Lib\Entity\Import\Customer\Customer as CustomerImport;
use Digidirect\AI\Model\Lib\Entity\Import\CustomerAddress\CustomerAddressInterfaceFactory;
use Digidirect\AI\Model\Lib\Entity\Import\CustomerAddress\CustomerAddress as CustomerAddressImport;
use Magento\ImportExport\Model\Import as MagentoImport;

/**
 * Class Customer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 */
class CustomerComposite extends ImportAbstract implements CustomerInterface
{
    const ENTITY_NAME = 'customer';

    /**
     * @var CustomerImport
     */
    protected $customerImport;

    /**
     * @var CustomerAddressImport
     */
    protected $customerAddressImport;

    /**
     * @var array
     */
    protected $importParameters = [];

    /**
     * @var array
     */
    protected $colNames;

    /**
     * CustomerComposite constructor.
     *
     * @param Validator $beforeSaveValidator
     * @param Validator $beforeUpdateValidator
     * @param LoggerInterface $logger
     * @param CustomerInterfaceFactory $customerImportFactory
     * @param CustomerAddressInterfaceFactory $customerAddressImportFactory
     * @param array $preparers
     */
    public function __construct(
        Validator $beforeSaveValidator,
        Validator $beforeUpdateValidator,
        LoggerInterface $logger,
        CustomerInterfaceFactory $customerImportFactory,
        CustomerAddressInterfaceFactory $customerAddressImportFactory,
        array $preparers = []
    ) {
        $this->customerImport = $customerImportFactory->create(['logger' => $logger]);
        $this->customerAddressImport = $customerAddressImportFactory->create(['logger' => $logger]);
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
        return $this->_saveBunch([$customer], $updateOnDuplicate);
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
        $result = $this->customerImport->saveBunch($customers, $updateOnDuplicate);
        $customersAddresses = $this->getCustomersAddresses($customers);
        if (!empty($customersAddresses)) {
            $result = $this->customerAddressImport->saveBunch($customersAddresses, $updateOnDuplicate);
        }

        return $result;
    }

    /**
     * @param array $customer
     * @return mixed
     */
    protected function _update(array $customer)
    {
        return $this->updateBunch([$customer]);
    }

    /**
     * @param array $customers
     * @return bool
     */
    protected function _updateBunch(array $customers)
    {
        $result = $this->customerImport->updateBunch($customers);
        $customersAddresses = $this->getCustomersAddresses($customers);
        if (!empty($customersAddresses)) {
            $result = $this->customerAddressImport->updateBunch($customersAddresses);
        }

        return $result;
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
        return $this->customerImport->deleteCustomer($customerEmail, $websiteCode);
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
     * @param array $customers
     * @return bool
     */
    public function deleteBunch(array $customers)
    {
        try {
            return $this->customerImport->deleteBunch($customers);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     *
     *
     * @param array $customers
     * @return array
     */
    protected function getCustomersAddresses(array $customers)
    {
        $customersAddresses = [];
        foreach ($customers as $customer) {
            $customerAddresses = $customer['addresses'] ?? [];
            if (empty($customerAddresses)) {
                continue;
            }

            foreach ($customerAddresses as $address) {
                $address['_email'] = $address['_email'] ?? $customer['email'] ?? '';
                $address['_entity_id'] = $address['_entity_id'] ?? '';
                $address['_website'] = $address['_website'] ?? $customer['_website'] ?? '';
                $customersAddresses[] = $address;
            }
        }

        return $customersAddresses;
    }
}
