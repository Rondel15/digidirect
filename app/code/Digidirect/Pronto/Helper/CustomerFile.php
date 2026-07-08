<?php
namespace Digidirect\Pronto\Helper;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;

class CustomerFile extends AbstractHelper
{
    protected $_customerFactory;
    protected $_addressFactory;
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory
    ) {

        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->_customerFactory = $customerFactory;
        $this->_addressFactory = $addressFactory;
    }

    public function getCustomerFile()
    {
        $filepath = 'export/customer_entity_new_06.csv';
        $this->directory->create('export');
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        $header = ['Id','CustomerGroupId','Email','FirstName','LastName','MiddleName','Prefix','Suffix',
            'DateOfBirth','Gender','TaxVAT','DisableAutoGroupChange','DefaultBillingAddress','DefaultShippingAddress',
            'SendWelcomeEmailFrom','WebsiteId','Pronto Account ID',
            'ProntoAccountName','isVisited','created_at','updated_at','Phone','PostCode','QFFNumber'];

        $stream->writeCsv($header);
        $collection = $this->getCustomerCollection();
        foreach ($collection as $customer) {

            $email = $customer->getEmail();
            if (str_contains($email, 'westfield.com')) {
                continue;
            }
            if (str_contains($email, 'catch.com.au')) {
                continue;
            }
            if (str_contains($email, 'marketplace.amazon.com.au')) {
                continue;
            }
            if (str_contains($email, 'mydeal.com.au')) {
                continue;
            }
            if (str_contains($email, 'members.ebay.com')) {
                continue;
            }
            if (str_contains($email, 'woolworths.com.au')) {
                continue;
            }

            $firstname = "";
            $lastname = "";
            $name = $customer->getName();
            if(!empty($name))
            {
                $name = explode(" ", $name);
                $firstname = $name[0];
                $lastname = $name[1];

            }
            $data = [];
            $data[] = $customer->getId();
            $data[] = $customer->getCustomerGroupId();
            $data[] = $customer->getEmail();
            $data[] = $firstname;
            $data[] = $lastname;
            $data[] = $customer->getMiddleName();
            $data[] = $customer->getPrefix();
            $data[] = $customer->getSuffix();
            $data[] = $customer->getDob();
            $data[] = $customer->getGender();
            $data[] = $customer->getTaxVAT();
            $data[] = $customer->getDisableAutoGroupChange();
            $billingAddressId = $customer->getDefaultBilling();
            $billingAddress = $this->_addressFactory->create()->load($billingAddressId);
            $strt = $billingAddress->getStreet();
            if(is_array($strt))
            {
                $strt = implode(",", $strt);
            }
            $city = $billingAddress->getCity();
            $region = $billingAddress->getRegion();
            $postcode = $billingAddress->getPostcode();
            $countrycode = $billingAddress->getCountryId();
            $billAddress = $strt. " ".$city." ".$region." ".$postcode." ".$countrycode;
            $data[] = $billAddress;
            $shippingAddressId = $customer->getDefaultShipping();
            $shippingAddress = $this->_addressFactory->create()->load($shippingAddressId);
            $strtS = $shippingAddress->getStreet();
            if(is_array($strtS))
            {
                $strtS = implode(",", $strtS);
            }
            $cityS = $billingAddress->getCity();
            $regionS = $billingAddress->getRegion();
            $postcodeS = $billingAddress->getPostcode();
            $countrycodeS = $billingAddress->getCountryId();
            $shipAddress = $strtS. " ".$cityS." ".$regionS." ".$postcodeS." ".$countrycodeS;

            $data[] = $shipAddress;
            $data[] = $customer->getSendWelcomeEmailFrom();
            $data[] = $customer->getWebsiteId();
            $data[] = $customer->getProntoAccountID();
            $data[] = $customer->getProntoAccountName();
            $data[] = $customer->getisVisited();
            $data[] = $customer->getCreatedAt();
            $data[] = $customer->getUpdatedAt();
            $data[] = $shippingAddress->getTelephone();
            $data[] = $postcode;
            $data[] = $customer->getQffNumber();
            $stream->writeCsv($data);
        }
    }
    public function getCustomerCollection()
    {
        $collection = $this->_customerFactory->create();
        $collection->addAttributeToSelect('*')
        ->addFieldToFilter('entity_id', array('gteq' => 1001934)); //2 - 415951 //3 - 577283 //4 - 824988 1001929
        $collection->setPageSize(50000); // fetching only x records
        return $collection;
    }
}
