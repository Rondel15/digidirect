<?php

namespace Digidirect\InvoiceEmail\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Digidirect\AbstractEntity\Model\AbstractEntityRepository;
use Digidirect\InvoiceIncrementId\Model\IncrementIdUpdater;
use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class InvoiceEmail extends AbstractHelper
{

    /**
    * @var Curl
    */
    protected $curl;

    protected $_orderCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var array
     */
    protected $warehouseCode = [];

    /**
     * @var SourceItemRepositoryInterface
     */
    protected $sourceItemRepository;

    /**
     * @var AbstractEntityRepository
     */
    protected $abstractEntityRepository;

    /**
     * @var IncrementIdUpdater
     */
    protected $incrementIdUpdater;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CustomerInterface[]|array
     */
    protected $customer = [];

    private $timezone;

    /**
     * @var Country
     */
    public $countryFactory;
    
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    
    protected $date;


    public function __construct(
        Curl $curl,
        JsonSerializer $jsonSerializer,
        \Magento\InventoryApi\Api\GetSourceItemsBySkuInterface $sourceItemsBySku,
        \Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceItemsSaveInterface,
        \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItemFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceItemRepositoryInterface $sourceItemRepository,
        AbstractEntityRepository $abstractEntityRepository,
        IncrementIdUpdater $incrementIdUpdater,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        CountryFactory $countryFactory,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    )
    {
        $this->curl = $curl;
        $this->jsonSerializer = $jsonSerializer;
        $this->sourceItemsBySku = $sourceItemsBySku;
        $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->abstractEntityRepository = $abstractEntityRepository;
        $this->incrementIdUpdater = $incrementIdUpdater;
        $this->customerRepository = $customerRepository;
        $this->timezone = $timezone;
        $this->countryFactory = $countryFactory;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->date = $date;
    }

    public function sendInvoiceEmail($test) {

        $orders = $this->getOrderCollection();
        $counter = 0;
        
        //$order = $this->order->create()->loadByIncrementId($id);

        foreach ($orders as $order)
        {
            if($test)
            {
                echo "orders <br>";
            }
            
            $customerFirstName = $order->getCustomerFirstname();
            $customerFullName = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
            $customerEmail = $order->getCustomerEmail();
            $orderNumber = $order->getIncrementId();
            $orderSubtotal = round($order->getSubtotal(), 2);
            $orderGrandTotal = round($order->getGrandtotal(), 2);
            $couponDiscount = round($order->getBaseDiscountAmount(), 2);
            
            $billingAddress = $order->getBillingAddress();
            $billingStreet = $billingAddress->getStreet();
            
            if(is_array($billingStreet))
            {
                $billingStreet = implode(",", $billingStreet);
            }
            $billingCity = $billingAddress->getCity();
            $billingRegion = $billingAddress->getRegion();
            $billingPostal = $billingAddress->getPostcode();
            $billingCountry = $billingAddress->getCountryId();
            $billingAddressConcat = $billingStreet ."<br>". $billingCity ."<br>". $billingRegion ."<br>". $billingPostal ." ". $billingCountry;
            
            $shippingAddress = $order->getShippingAddress();
            $shippingStreet = $shippingAddress->getStreet();
            if(is_array($shippingStreet))
            {
                $shippingStreet = implode(",", $shippingStreet);
            }
            $shippingCity = $shippingAddress->getCity();
            $shippingRegion = $shippingAddress->getRegion();
            $shippingPostal = $shippingAddress->getPostcode();
            $shippingCountry = $shippingAddress->getCountryId();
            $shippingAddressConcat = $shippingStreet ."<br>". $shippingCity ."<br>". $shippingRegion ."<br>". $shippingPostal ." ". $shippingCountry;
            $shippingAmount = round($order->getShippingAmount(), 2);
            
            //$totalFOrGst = round($orderGrandTotal - $shippingAmount, 2);
            $totalEx = round($orderGrandTotal / 1.1, 2);
            $gst = round($orderGrandTotal - $totalEx, 2);
            
            $invoiceDate = date('d/m/Y', strtotime($this->date->gmtDate()));
            
            $tracksCollection = $order->getTracksCollection();
            $trackTitleString = "";
            $trackNumberString = "";
            
            foreach ($tracksCollection->getItems() as $track) {
                $trackTitleString .= $track->getTitle();
                $trackNumberString .= $track->getTrackNumber();
            }

            $trackTitle = $trackTitleString; //$order->getTracksCollection()->fetchItem()->getTitle();
            $trackNumber = $trackNumberString; //$order->getTracksCollection()->fetchItem()->getTrackNumber(); 
        
            if($test)
            {
                echo "order -" .$orderNumber." to ".$customerEmail." <br>";
            }
            
            $items = $order->getAllItems();
            $store = $this->storeManager->getStore();

            $templateParams = [
                'store' => $store,
                'order' => $order,
                'order_number' => $orderNumber, 
                'order_subtotal' => $orderSubtotal, 
                'order_grandtotal' => $orderGrandTotal,
                'total_ex' => $totalEx,
                'gst' => $gst,
                'coupon_discount' => $couponDiscount,
                'invoice_date' => $invoiceDate, 
                'customer_firstname' => $customerFirstName, 
                'customer_fullname' => $customerFullName,
                'billingAddress' => $billingAddressConcat, 
                'shippingAddress' => $shippingAddressConcat,
                'shippingAmount' => $shippingAmount,
                'trackTitle' => $trackTitle, 
                'trackNumber' => $trackNumber, 
                'items' => $items
            ];

            $transport = $this->transportBuilder->setTemplateIdentifier(
                'digidirect_invoice_email_template'
                )->setTemplateOptions(
                    ['area' => 'frontend', 'store' => $store->getId()]
                )->addTo(
                    $customerEmail, $customerFirstName
                )->setTemplateVars(
                    $templateParams
                )->setFrom(
                    'general'
                )->addBcc(
                    'clint@kayweb.com.au' 
                )->getTransport();

            try {
                $transport->sendMessage();
            } catch (\Exception $e) {
                if($test)
                {
                    echo $e->getMessage()."<br>";
                }
                $this->logger->critical($e->getMessage());
                
            }
            
            $order->setData('invoice_email', 1);
            $order->save();
            
        }
        return true;
    }

    public function getOrderCollection()
    {
        
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', array('gt' => 1419436))
            ->addFieldToFilter('store_id', array('eq' => 1))
            ->addFieldToFilter('status', ['in' => ['complete','completed']])
            ->addFieldToFilter('invoice_email', array('eq' => 0))
            ->addFieldToFilter('shipping_description', array('neq' =>'Pick Up in Store - Click and Collect Shipping'))
            ->setOrder('created_at', 'asc');

        return $collection;

    }

    
}
