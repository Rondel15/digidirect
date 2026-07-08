<?php

namespace Digidirect\ReadytoPickup\Helper;

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

class ReadytoPickup extends AbstractHelper
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
    protected $relocateWarehouseMap = [
        'MELB' => 'SWHS',
        'CANN' => 'SWHS',
        'SWHS' => 'MELB'
    ];
    /**
     * @var array
     */
    protected $repCodeForPickUp = [
        '11' => 'S7P',
        '31' => 'B4P',
        '17' => 'M1P',
        '21' => 'M6P',
        '23' => 'C3P',
        '1'  => 'S7P',
        '4'  => 'B4P',
        '7'  => 'M1P',
        '10' => 'B5P',
        '13' => 'M6P',
        '16' => 'C3P',
        '19' => 'B5P',
        '32' => 'P4P',
        '35' => 'C9W'
    ];
    protected $invCodeAll = [
        'BOND',
        'BRIS',
        'CANN',
        'MELB',
        'MIRA',
        'PARR',
        'SWHS',
        'SYDN'
    ];
    protected $invCode = [
        'BRIS',
        'CANN',
        'MELB',
        'MIRA',
        'SWHS',
        'SYDN'
    ];
    /**
     * @var array
     */
    protected $repDispatchWarehouseMap = [
        'MELB' => '85',
        'CANN' => 'C3W',
        'SWHS' => 'C9W'
    ];
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
        LoggerInterface $logger)
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
    }
    public function sendReadytoPickupEmail() {
        //$this->logger->info("sendReadytoPickupEmail()");
        //get order data
        $orders = $this->getOrderCollection();
        $counter = 0;
        foreach ($orders as $order)
        {
            //Check store hours if source is SWHS
            $shwhStoreHours = $this->getStoreSwhsStoreHOurs($order);
            //Send ReadytoPickup Confirmation Email
            $customerFirstName = $order->getCustomerFirstname();
            $customerFullName = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
            $customerEmail = $order->getCustomerEmail();
            $orderNumber = $order->getIncrementId();
            $store = $this->storeManager->getStore();
            $templateParams = ['store' => $store,
                'order_number' => $orderNumber,
                'customer_firstname' => $customerFirstName,
                'customer_fullname' => $customerFullName,
                'swhs_store_hours' => $shwhStoreHours
            ];
            $transport = $this->transportBuilder->setTemplateIdentifier(
                'digidirect_readytopickup_email_template'
                )->setTemplateOptions(
                    ['area' => 'frontend', 'store' => $store->getId()]
                )->addTo(
                    $customerEmail, $customerFirstName
                )->setTemplateVars(
                    $templateParams
                )->setFrom(
                    'general'
                )->addBcc(
                    'rondel.d@digidirect.com.au'
                )->getTransport();
            try {
                // Send an email
                $transport->sendMessage();
                //End Send ReadytoPickup Confirmation Email
                //Change ReadytoPickup Status
                $order->setData('pickup_email', 1);
                $order->save();
                //End Change ReadytoPickup Status
            } catch (\Exception $e) {
                // Write a log message whenever get errors
                $this->logger->critical($e->getMessage());
            }
        }
        return true;
    }
    
    public function getOrderCollection()
    {
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', array('gt' => 1139532))
            ->addFieldToFilter('status', array('eq' => 'complete'))
            ->addFieldToFilter('pickup_email', array('eq' => 0))
            ->addFieldToFilter('shipping_description', array('eq' =>'Pick Up in Store - Click and Collect Shipping'))
            ->setOrder('created_at', 'asc');
        return $collection;
    }
    
    /**
     * @param OrderInterface $order
     * @return int|null
     */
    protected function getCollectPlaceId(OrderInterface $order) {
        foreach ($order->getAllVisibleItems() as $item) {
            if ($collectPlaceId = $item->getCollectPlaceId()) {
                return $collectPlaceId;
            }
        }
        return null;
    }
    
    public function getStoreSwhsStoreHOurs(OrderInterface $order) {
        $storeHours = "";
        //if (!isset($this->warehouseCode[$order->getEntityId()])) {
        $whse = '';
        if ($order->getShippingMethod() == 'collect_collect') {
            if ($collectPlaceId = $this->getCollectPlaceId($order)) {
                $whse = $this->abstractEntityRepository->getById($collectPlaceId)->getCode();
                if ($whse == "SWHS") {
                    $storeHours = "<span>St. Peters Hours</span>
                    <table>
                        <tbody>
                            <tr>
                                <td>Monday</td>
                                <td>9:00 AM - 5:00 PM</td>
                            </tr>
                            <tr>
                                <td>Tuesday</td>
                                <td>9:00 AM - 5:00 PM</td>
                            </tr>
                            <tr>
                                <td>Wednesday</td>
                                <td>9:00 AM - 5:00 PM</td>
                            </tr>
                            <tr>
                                <td>Thursday</td>
                                <td>9:00 AM - 5:00 PM</td>
                            </tr>
                            <tr>
                                <td>Friday</td>
                                <td>9:00 AM - 5:00 PM</td>
                            </tr>
                            <tr>
                                <td>Saturday</td>
                                <td>CLOSED</td>
                            </tr>
                            <tr>
                                <td>Sunday</td>
                                <td>CLOSED</td>
                            </tr>
                        </tbody>
                    </table>
                    <span>Address: 11 Burrows Road South, St Peters New South Wales 2044</span>";
                } elseif ($whse == "BOND") {
                    $storeHours = "<span>Bondi Junction Hours</span>
                    <table>
                        <tbody>
                            <tr>
                                <td>Monday</td>
                                <td>9:30 AM - 6:00 PM</td>
                            </tr>
                            <tr>
                                <td>Tuesday</td>
                                <td>9:30 AM - 6:00 PM</td>
                            </tr>
                            <tr>
                                <td>Wednesday</td>
                                <td>9:30 AM - 6:00 PM</td>
                            </tr>
                            <tr>
                                <td>Thursday</td>
                                <td>9:30 AM - 9:00 PM</td>
                            </tr>
                            <tr>
                                <td>Friday</td>
                                <td>9:30 AM - 6:00 PM</td>
                            </tr>
                            <tr>
                                <td>Saturday</td>
                                <td>10:00 AM - 5:00 PM</td>
                            </tr>
                            <tr>
                                <td>Sunday</td>
                                <td>10:00 AM - 5:00 PM</td>
                            </tr>
                        </tbody>
                    </table>
                    <span>Address: Level 1 Shop 1044/500 Oxford Street Bondi Junction New South Wales 2022</span>";
                } elseif ($whse == "CANN") {
                    $storeHours = "<span>Cannington Hours</span>
                    <table>
                        <tbody>
                            <tr>
                                <td>Monday</td>
                                <td>9:00 AM - 5:30 PM</td>
                            </tr>
                            <tr>
                                <td>Tuesday</td>
                                <td>9:00 AM - 5:30 PM</td>
                            </tr>
                            <tr>
                                <td>Wednesday</td>
                                <td>9:00 AM - 5:30 PM</td>
                            </tr>
                            <tr>
                                <td>Thursday</td>
                                <td>9:00 AM - 5:30 PM</td>
                            </tr>
                            <tr>
                                <td>Friday</td>
                                <td>9:00 AM - 5:30 PM</td>
                            </tr>
                            <tr>
                                <td>Saturday</td>
                                <td>9:00 AM - 5:00 PM</td>
                            </tr>
                            <tr>
                                <td>Sunday</td>
                                <td>11:00 AM - 4:00 PM</td>
                            </tr>
                        </tbody>
                    </table>
                    <span>Address: 12 Cecil Ave Cannington Western Australia 6107</span>";
                } elseif ($whse == "PARR") {
                    $storeHours = "<span>Parramatta Hours</span>
                    <table>
                        <tbody>
                            <tr>
                                <td>Monday</td>
                                <td>9:30 AM - 6:00 PM</td>
                            </tr>
                            <tr>
                                <td>Tuesday</td>
                                <td>9:30 AM - 6:00 PM</td>
                            </tr>
                            <tr>
                                <td>Wednesday</td>
                                <td>9:30 AM - 6:00 PM</td>
                            </tr>
                            <tr>
                                <td>Thursday</td>
                                <td>9:30 AM - 9:00 PM</td>
                            </tr>
                            <tr>
                                <td>Friday</td>
                                <td>9:30 AM - 6:00 PM</td>
                            </tr>
                            <tr>
                                <td>Saturday</td>
                                <td>10:00 AM - 5:00 PM</td>
                            </tr>
                            <tr>
                                <td>Sunday</td>
                                <td>10:00 AM - 5:00 PM</td>
                            </tr>
                        </tbody>
                    </table>
                    <span>Address: Shop 2101-2103 Level 2 (159 Church Street) Parramatta New South Wales 2150</span>";
                } elseif ($whse == "SYDN") {
                    $storeHours = "<span>Sydney CBD Hours</span>
                    <table>
                        <tbody>
                            <tr>
                                <td>Monday</td>
                                <td>9:30 AM - 6:00 PM</td>
                            </tr>
                            <tr>
                                <td>Tuesday</td>
                                <td>9:30 AM - 6:00 PM</td>
                            </tr>
                            <tr>
                                <td>Wednesday</td>
                                <td>9:30 AM - 6:00 PM</td>
                            </tr>
                            <tr>
                                <td>Thursday</td>
                                <td>9:30 AM - 7:00 PM</td>
                            </tr>
                            <tr>
                                <td>Friday</td>
                                <td>9:30 AM - 6:00 PM</td>
                            </tr>
                            <tr>
                                <td>Saturday</td>
                                <td>10:00 AM - 5:00 PM</td>
                            </tr>
                            <tr>
                                <td>Sunday</td>
                                <td>10:00 AM - 5:00 PM</td>
                            </tr>
                        </tbody>
                    </table>
                    <span>Address: Shop 3/75 King Street Sydney New South Wales 2000</span>";
                } elseif ($whse == "BRIS") {
                    $storeHours = "<span>Brisbane Hours</span>
                    <table>
                        <tbody>
                            <tr>
                                <td>Monday</td>
                                <td>9:30 AM - 5:30 PM</td>
                            </tr>
                            <tr>
                                <td>Tuesday</td>
                                <td>9:30 AM - 5:30 PM</td>
                            </tr>
                            <tr>
                                <td>Wednesday</td>
                                <td>9:30 AM - 5:30 PM</td>
                            </tr>
                            <tr>
                                <td>Thursday</td>
                                <td>9:30 AM - 5:30 PM</td>
                            </tr>
                            <tr>
                                <td>Friday</td>
                                <td>9:00 AM - 6:00 PM</td>
                            </tr>
                            <tr>
                                <td>Saturday</td>
                                <td>10:00 AM - 4:00 PM</td>
                            </tr>
                            <tr>
                                <td>Sunday</td>
                                <td>10:00 AM - 3:00 PM</td>
                            </tr>
                        </tbody>
                    </table>
                    <span>Address: 166 Adelaide Street Brisbane Queensland 4000</span>";
                } elseif ($whse == "MELB") {
                    $storeHours = "<span>Melbourne CBD Hours</span>
                    <table>
                        <tbody>
                            <tr>
                                <td>Monday</td>
                                <td>9:30 AM - 6:00 PM</td>
                            </tr>
                            <tr>
                                <td>Tuesday</td>
                                <td>9:30 AM - 6:00 PM</td>
                            </tr>
                            <tr>
                                <td>Wednesday</td>
                                <td>9:30 AM - 6:00 PM</td>
                            </tr>
                            <tr>
                                <td>Thursday</td>
                                <td>9:30 AM - 6:00 PM</td>
                            </tr>
                            <tr>
                                <td>Friday</td>
                                <td>9:30 AM - 6:00 PM</td>
                            </tr>
                            <tr>
                                <td>Saturday</td>
                                <td>10:00 AM - 5:00 PM</td>
                            </tr>
                            <tr>
                                <td>Sunday</td>
                                <td>11:00 AM - 5:00 PM</td>
                            </tr>
                        </tbody>
                    </table>
                    <span>Address: 217 Elizabeth Street Melbourne Victoria 3000</span>";
                } elseif ($whse == "MIRA") {
                    $storeHours = "<span>Miranda Hours</span>
                    <table>
                        <tbody>
                            <tr>
                                <td>Monday</td>
                                <td>9:30 AM - 8:00 PM</td>
                            </tr>
                            <tr>
                                <td>Tuesday</td>
                                <td>9:30 AM - 5:30 PM</td>
                            </tr>
                            <tr>
                                <td>Wednesday</td>
                                <td>9:30 AM - 5:30 PM</td>
                            </tr>
                            <tr>
                                <td>Thursday</td>
                                <td>9:30 AM - 8:00 PM</td>
                            </tr>
                            <tr>
                                <td>Friday</td>
                                <td>9:30 AM - 5:30 PM</td>
                            </tr>
                            <tr>
                                <td>Saturday</td>
                                <td>9:30 AM - 5:00 PM</td>
                            </tr>
                            <tr>
                                <td>Sunday</td>
                                <td>10:00 AM - 5:00 PM</td>
                            </tr>
                        </tbody>
                    </table>
                    <span>Address: Shop 1098/600 Kingsway Miranda New South Wales 2228</span>";
                }
            }
        }
        //}
        return $storeHours;
    }
}