<?php

namespace Digidirect\Pronto\Helper;

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

class TestPronto extends AbstractHelper
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
        'MELB' => '3WHS',
        'CANN' => '3WHS',
        '3WHS' => 'MELB'
    ];

    /**
     * @var array
     */
    protected $repCodeForPickUp = [
        '11' => 'S7P',
        '31' => 'B4P',
        '17' => 'M1P',
        '21' => 'M6P',
        '19' => 'B5P',
        '23' => 'C3P',
        '1'  => 'S7P',
        '4'  => 'B4P',
        '7'  => 'M1P',
        '10' => 'B5P',
        '13' => 'M6P',
        '16' => 'C3P',
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
        '3WHS',
        'SYDN'
    ];

    protected $invCode = [
        'BRIS',
        'CANN',
        'MELB',
        'MIRA',
        '3WHS',
        'SYDN'
    ];

    protected $acceGroup = [
        "A1A1",
        "A1B1",
        "A1C1",
        "A1D1",
        "A1E1",
        "A1F1",
        "A1G1",
        "A1H1",
        "A1I1",
        "A1J1",
        "A1K1",
        "A1L1",
        "A1M1",
        "A1N1",
        "A1O1",
        "A1P1",
        "B1C2",
        "C1A1",
        "D1A1",
        "D1B1",
        "D1C1",
        "G1A1",
        "H1A1",
        "H1B1",
        "H1C1",
        "I1A1",
        "I1B1",
        "I1C1",
        "J1A1",
        "J1B1",
        "J1C1",
        "K1A1",
        "N1A1",
        "N1B1",
        "N1C1",
        "N1D1",
        "N1E1",
        "N1F1",
        "N1G1"
    ];

    /**
     * @var array
     */
    protected $repDispatchWarehouseMap = [
        'MELB' => '85',
        'CANN' => 'C3W',
        '3WHS' => 'C9W'
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

    /**
     * @var LoggerInterface
     */
    protected $logger;

    private $timezone;

    /**
     * @var Country
     */
    public $countryFactory;

    protected $productDigiprot;

    protected $currentseller;
    protected $syncedseller;

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
        \Digidirect\CustomOrderLog\Logger\Logger $logger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        CountryFactory $countryFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory)
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
        $this->logger = $logger;
        $this->timezone = $timezone;
        $this->countryFactory = $countryFactory;
        $this->productFactory = $productFactory;

    }


    public function getOrderCollection()
    {
        $now = new \DateTime();
        $fromDate = date('Y-m-d 00:00:00',strtotime("2021-07-05"));
        $toDate = $now->format('Y-m-d h:i:s');
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('pronto_order_number', array('null' => true))
            //->addFieldToFilter('created_at',$now->format('Y-m-d'));
            ->addFieldToFilter('created_at', array('gteq' => $fromDate))
            ->addFieldToFilter('created_at', array('lteq' => $toDate))
            ->setOrder('created_at', 'desc');

        return $collection;

    }

    public function getPaymentType($paymentInstance){

        //echo "<br >get payment type ". $paymentInstance->getMethod();
        $type = "";
        $payment = $paymentInstance->getMethod();
        echo "<br >get payment type ".$payment."<br>";
        switch ($payment) {
            case "braintree":
                $type = 'BT';
                break;
            case "checkmo":
                $type = 'H';
                break;
            case "free":
                $type = 'VI';
                break;
            case "m2epropayment":
                $type = 'EB';
                break;
            case "braintree_paypal":
                $type = 'PY';
                break;
            case "banktransfer":
                $type = 'Y';
                break;
            case "paybympcatch":
                $type = 'CA';
                break;
            case "zippayment":
                $type = "ZM";
                break;
            case "zipmoneypayment":
                $type = "ZM";
                break;
            case "zipmoney":
                $type = "ZM";
                break;
            case "zip":
                $type = "ZM";
                break;
            case "braintree_googlepay":
                $type = "BT";
                break;
            case "klarna_kp":
                $type = "KL";
                break;
            case "braintree_applepay":
                $type = "BT";
                break;
            case "latipay":
                $type = 'LP';
                break;
            case "latitude":
                $type = 'LA';
                break;
            case "instant":
                $type = 'IP';
                break;
            case "paypal_express":
                $type = 'PX';
                break;
            case "afterpay":
                $type = 'AP';
                break;
            default:
                break;
        }

        return $type;
    }

    public function getWarehouse(OrderInterface $order) {
        if (!isset($this->warehouseCode[$order->getEntityId()])) {
            $whse = '';

            if ($order->getShippingMethod() == 'collect_collect') {
                if ($collectPlaceId = $this->getCollectPlaceId($order)) {
                    //$whse = $this->abstractEntityRepository->getById($collectPlaceId)->getCode();

                    switch ($collectPlaceId) {
                        case '1':
                            $whse = 'SYDN';
                            break;
                        case '7':
                            $whse = 'MELB';
                            break;
                        case '10':
                            $whse = 'BRIS';
                            break;
                        case '13':
                            $whse = 'MIRA';
                            break;
                        case '16':
                            $whse = 'CANN';
                            break;
                        case '31':
                            $whse = 'BOND';
                            break;
                        case '32':
                            $whse = 'PARR';
                            break;
                        default:
                            $whse = '3WHS';
                            break;
                    }

                    echo 'debug collectPlaceId -'.$collectPlaceId.' whse - '.$whse;

                }
            } elseif ($order->getShippingAddress()) {
//                $whse = $this->getWarehouseByRegionCode($order->getShippingAddress()->getRegionCode());
//                $skus = $this->getProductsSkus($order);
//                if (!$this->isProductsInStock($whse, $skus) && isset($this->relocateWarehouseMap[$whse]) && $this->isProductsInStock($this->relocateWarehouseMap[$whse], $skus)) {
//                    $whse = $this->relocateWarehouseMap[$whse];
//                }
                //requestd by Emmanuel
                $whse = "3WHS";
            }
            $this->warehouseCode[$order->getEntityId()] = $whse;
        }
        return $this->warehouseCode[$order->getEntityId()];
    }

    public function getRep(OrderInterface $order) {
        if ($order->getShippingMethod() == 'collect_collect') {
            if ($collectPlaceId = $this->getCollectPlaceId($order)) {
                echo "collectPlaceId -".$collectPlaceId."<br/>";
                $mapping = $this->repCodeForPickUp;
                return $mapping[$collectPlaceId] ?? '';
            }
            return '';
        }
        echo 'WHSE - '. $this->repDispatchWarehouseMap[$this->getWarehouse($order)] ?? '';
        return $this->repDispatchWarehouseMap[$this->getWarehouse($order)] ?? '';
    }

    protected function getWarehouseByRegionCode($regionCode) {
        return '3WHS';
    }

    /**
     * @param OrderInterface $order
     * @return array
     */
    protected function getProductsSkus(OrderInterface $order) {
        $skus = [];
        /** @var $item \Magento\Sales\Model\Order\Item */
        foreach ($order->getAllVisibleItems() as $item) {
            if (!$item->getIsVirtual()) {
                $skus = array_merge($skus, $this->getSkusByProductType($item));
            }
        }

        return $skus;
    }

    /**
     * @param string $sourceCode
     * @param array $productsSkus
     * @return bool
     */
    protected function isProductsInStock($sourceCode, array $productsSkus) {
        $sourceItems = $this->getSourceItemBySourceCodeAndSku($sourceCode, $productsSkus);
        foreach ($sourceItems as $sourceItem) {
            if (!$sourceItem->getQuantity() || $sourceItem->getStatus() !== SourceItemInterface::STATUS_IN_STOCK) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $sourceCode
     * @param array $productsSkus
     * @return bool
     */
    protected function isProductsInStockMP($sourceCode, array $productsSkus) {
        $sourceItems = $this->getSourceItemBySourceCodeAndSku($sourceCode, $productsSkus);
        foreach ($sourceItems as $sourceItem) {
            echo "sourceitem ".$sourceItem->getSku() ." - ".$sourceItem->getStatus() ." -".$sourceCode. " - ".$sourceItem->getQuantity()."<br>";
            $qty = $sourceItem->getQuantity();
            $qty = (int)$qty;
            if ($qty > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $sourceCode
     * @param array $productsSkus
     * @return bool
     */
    protected function isProductsInStockAll($sourceCode, array $productsSkus) {
        $sourceItems = $this->getSourceItemBySourceCodeAndSku($sourceCode, $productsSkus);

        foreach ($sourceItems as $sourceItem) {
            echo "sourceitem ".$sourceItem->getSku() ." - ".$sourceItem->getStatus() ." -".$sourceCode. " - ".$sourceItem->getQuantity()."<br>";
            $qty = $sourceItem->getQuantity();
            $qty = (int)$qty;
            if ($qty < 1) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param OrderItemInterface $item
     * @return array
     */
    protected function getSkusByProductType(OrderItemInterface $item) {
        switch ($item->getProductType()) {
            case \Magento\Bundle\Model\Product\Type::TYPE_CODE:
                return $this->getOrderLinesByBundle($item);
            default:
                return [$item->getSku()];
        }
    }

    /**
     * @param string $sourceCode
     * @param string $sku
     * @return SourceItemInterface[]
     */
    protected function getSourceItemBySourceCodeAndSku($sourceCode, array $sku) {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SOURCE_CODE, $sourceCode)
            ->addFilter(SourceItemInterface::SKU, $sku, 'in')
            ->create();
        $sourceItemsResult = $this->sourceItemRepository->getList($searchCriteria);
        return $sourceItemsResult->getItems();
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

    /**
     * @param OrderInterface $order
     * @param string $attributeCode
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getCustomerAttributeValue(OrderInterface $order, $attributeCode) {
        $result = '';
        if (!$order->getCustomerIsGuest() && !isset($this->customer[$order->getEntityId()])) {
            //echo "<br/>not guest";
            try {
                $this->customer[$order->getEntityId()] = $this->customerRepository->getById($order->getCustomerId());
            } catch (Exception $ex) {
                return $result;
            }

        }

        if (isset($this->customer[$order->getEntityId()])) {
            //echo "<br />get entity";
            $customer = $this->customer[$order->getEntityId()];
            $attribute = $customer->getCustomAttribute($attributeCode);
            $result = $attribute ? $attribute->getValue() : '';
        }

        return $result;
    }

    /**
     * @param OrderInterface|Order $order
     * @return string
     */
    public function getAccountName(OrderInterface $order) {
        //echo "<br /> get accountname. ";
        $accountName = $this->getCustomerAttributeValue($order, 'pronto_account_name');
        if (empty($accountName)) {
            $address = $order->getShippingAddress() ?? $order->getBillingAddress();
            $accountName = $address->getName();
        }
        $aReplace = array('(', ')','[',']','{','}');
        $accountName = str_replace($aReplace , '', $accountName);
        return $accountName;
    }

    /**
     * @param OrderInterface|Order $order
     * @return string
     */
    public function getAccount(OrderInterface $order) {
        return $this->getCustomerAttributeValue($order, 'pronto_account_id');
    }

    public function prontoStatus(OrderInterface $order) {
        $status = $order->getState();
        $prontoStatus = "I";
        if($status == "holded")
        {
            $prontoStatus = "H";
        }

        return $prontoStatus;
    }

    public function orderPostTec($orderId, $date, $size, $page, $test)
    {

        settype($test,"integer");
        settype($size,"integer");
        //get order data
        $orders = $this->getTestOrderCollection($orderId, $date, $size, $page);
        $counter = 0;
        foreach ($orders as $order)
        {
            $data = array();
            $counter++;
            $this->currentseller = "";
            $this->syncedseller = "";
            $state = $order->getState();
            /* @var $order \Magento\Sales\Model\Order */

            if(!$test)
            {
                if ($order->getState() == 'canceled') {
                    continue;
                }

                if ($order->getState() == 'pending') {
                    continue;
                }
            }


            $prontoOrderNumber = $order->getData('pronto_order_number');
            if(is_numeric($prontoOrderNumber))
            {
                echo "Has pronto ". $prontoOrderNumber."<br/>";
                if(!$test)
                {
                    continue;
                }

            }

            $orderId = $order->getIncrementId();
            $entityId = $order->getId();
            $this->logger->info('Pronto Order Sync - '.$orderId);
            $isMarketPlace = false;
            //Amazon Logic
            $wrehs = $this->getWarehouse($order);
            echo 'wrehs '.$wrehs;
            $territory = "WEBS";
            if($wrehs != '3WHS')
            {
                if($wrehs != '')
                {
                    $territory = $wrehs;
                }

            }

            $accountname = $this->getAccountName($order);
            $account = $this->getAccount($order);

            $this->logger->info('Pronto Order AccountName - '.$accountname);
            $this->logger->info('Pronto Order Account - '.$account);

            $newaccount = "";
            $address = $order->getBillingAddress();
            $countrycode = $address->getCountryId();
            $countryName = "";
            if(isset($countrycode))
            {
                $country = $this->countryFactory->create()->loadByCode($countrycode);
                if ($country) {
                    $countryName = $country->getName();
                }
            }

            $amShipping = "";
            if (strpos($orderId, 'REEB') !== false) {

            }
            else
            {
                $amShipping = $order->getShippingDescription();
            }


            $this->logger->info('Pronto Order Shipping - '.$amShipping);

            $is_am_order = false;
            $is_am_fba = false;
            if (strpos($orderId, 'AM') !== false) {
                $is_am_order = true;
            }

            if($is_am_order){
                $rep = "AMAZON MFN";
                $account = "AMAZ02";
                if (strpos($amShipping, 'AFN') !== false) {
                    $rep = "AMAZON FBA";
                    $account = "AMAZ00";
                    if($countrycode == "NZ")
                    {
                        $account = "AMAZ01";
                    }

                    $wrehs = "AWHS";
                    //$territory = "AWHS";
                    $is_am_fba = true;
                }
                $territory = "MRKT";
                $isMarketPlace = true;

            }
            else
            {
                $rep = $this->getRep($order);
                if (strpos($orderId, 'EB') !== false) {
                    $rep ="EBAY";
                    $account = "EBAY00";
                    $territory = "MRKT";
                    $isMarketPlace = true;

                    if (strpos($orderId, 'REEB') !== false) {
                        $rep ="REEBELO";
                        $account = "REEB00";
                        $territory = "MRKT";
                        $isMarketPlace = true;

                        //redeploy
                    }
                }
                else if (strpos($orderId, 'CATCH') !== false) {
                    $rep ="CATCH";
                    $account = "CATC00";
                    $territory = "MRKT";
                    $isMarketPlace = true;
                }
                else if (strpos($orderId, 'MYD') !== false) {
                    $rep ="MYDEAL";
                    $account = "MYDE00";
                    $territory = "MRKT";
                    $isMarketPlace = true;
                }
                else if (strpos($orderId, 'WD') !== false) {
                    $rep ="WESTFIELD";
                    $account = "WEST00";
                    $territory = "MRKT";
                    $isMarketPlace = true;
                }
                else if (strpos($orderId, 'Q') !== false) {
                    $account = "QANT00";
                    $rep ="QANTAS";
                    $territory = "MRKT";
                    $isMarketPlace = true;
                }
                else if (strpos($orderId, 'WW') !== false) {
                    $rep ="WOOLWORTHS";
                    $account = "WOOL00";
                    $territory = "MRKT";
                    $isMarketPlace = true;
                    //for woolworths
                }
                else if (strpos($orderId, 'BU') !== false) {
                    $rep ="BUNNINGS";
                    $account = "BUNN01";
                    $territory = "MRKT";
                    $isMarketPlace = true;
                    //for woolworths
                }

            }

            echo "Rep ".$rep."<br/>";
            echo "Is marketplace ".$isMarketPlace."<br/>";
            $directToWhse = false;

            if($isMarketPlace)
            {
                echo "It is marketplace ".$isMarketPlace."<br/>";
                //check if all product has stock in 3WHS
                $skus = $this->getProductsSkus($order);
                //var_dump($skus);
                if ($this->isProductsInStockMP('3WHS', $skus)) {
                    $directToWhse = true;
                }
                echo "directToWhse ".$directToWhse."<br/>";
            }

            $contactname = $accountname;
            //check pronto if customer has an account.
            //if not, create customer account to pronto
            $created = $order->getCreatedAt();
            $created = $this->timezone->date(new \DateTime($created));
            $orderdate = $created->format('Y-m-d');

            $customertype = "WG";
            if (!empty($account) && !$order->getCustomerIsGuest()) {
                $customertype = "WA";
                $this->logger->info('Pronto Order Customer Not Guest - '.$amShipping);
            }

            if(!$isMarketPlace)
            {
                if($account == "WOOL00" || $account == "QANT00" ||  $account == "WEST00" ||  $account == "MYDE00" ||  $account == "CATC00" ||  $account == "EBAY00" || $account == "AMAZ01" || $account == "AMAZ02" || $account == "AMAZ00")
                {
                    $account = "";
                }
            }
            //comment to redeploy

            $customerEmail = $order->getCustomerEmail();
            $data['sales-order']['header']['accountname'] = $accountname;
            $data['sales-order']['header']['account'] = $account;
            $data['sales-order']['header']['order-date'] = $orderdate;
            $data['sales-order']['header']['warehouse'] = $wrehs;
            $data['sales-order']['header']['customer-type'] = $customertype;
            $data['sales-order']['header']['so-cust-type'] = $customertype;
            $data['sales-order']['header']['territory'] = $territory;
            $data['sales-order']['header']['rep'] = $rep;
            $data['sales-order']['header']['contactname'] = $contactname;
            $data['sales-order']['header']['email'] = $customerEmail;
            $data['sales-order']['header']['reference'] = $entityId;

            //changes
            $paymentInstance = $order->getPayment();

            //payment details

            //$methodInst = $paymentInstance->getMethodInstance();
            $method = $paymentInstance->getMethod();

            $payment_type = $this->getPaymentType($paymentInstance);
            echo "Payment type ".$payment_type."<br/>";
            $cc = "";

            $grandTotal = (double) $order->getBaseGrandTotal();
            echo "grandTotal - ".$grandTotal."<br/>";
            $subTotal = (double) $order->getBaseSubtotalInclTax();
            $tax = (double) $order->getBaseTaxAmount();
            $shipping = (double) $order->getBaseShippingInclTax();

            //Workaround clint Mar 3 23.
            $disregardshipping = false;
            $modifygrandtotal = false;
            $surcharge = $order->getPaymentFee();
//            if(!$isMarketPlace)
//            {
//                if($payment_type == 'BT' || $payment_type == 'PY')
//                {
//                    if($surcharge == '0.0000') //manually created orders
//                    {
//                        if($grandTotal <= 99)
//                        {
//                            $grandTotal = $grandTotal - 9.9;
//                            $disregardshipping = true;
//                        }
//                        $surcharge = $grandTotal * 0.0095;
//                        $grandTotal = $grandTotal + $surcharge;
//                        echo "new grandTotal - ".$grandTotal."<br/>";
//                        echo "surcharge - ".$surcharge."<br/>";
//
//                        $modifygrandtotal = true;
//                    }
//                }
//            }


            if($payment_type == 'BT')
            {
                $cc = $paymentInstance->getCcType();
            }

            if($directToWhse)
            {
                $data['sales-order']['header']['on-hold-reason-code'] = "";
                $data['sales-order']['header']['set-on-status'] = "P";

                if($is_am_fba)
                {
                    $data['sales-order']['header']['on-hold-reason-code'] = "WS";
                    $data['sales-order']['header']['set-on-status'] = "H";
                }

            }
            else
            {
                $data['sales-order']['header']['on-hold-reason-code'] = "WS";
                $data['sales-order']['header']['set-on-status'] = "H";
//                WF – Web Fraud  ( this would be orders flagged in BT or other platforms as needing a fraud check )
//                WS – Web Stock Shortage ( this would be an order placed on hold for a stock shortage reason. For example a marketplace order where there is no stock in 3WHS )
//                WP – Web Payment ( this would be for orders we cannot process because we need to apply payment example would be direct deposit but maybe also Studio 19 ?? )
                if($isMarketPlace) // since it did not go to $directToWhse, we assume there is no stock
                {
                    $data['sales-order']['header']['on-hold-reason-code'] = "WS";
                    $data['sales-order']['header']['set-on-status'] = "H";
                }
                else
                {

                    //check for stock
                    //check for fraud BT

                    $skus = $this->getProductsSkus($order);
                    $instockInv = 0;
                    //use warehouse
                    if ($this->isProductsInStockAll($wrehs, $skus)) {
                        $instockInv = 1;
                        echo "instockInv ".$instockInv."<br>";
                    }
//                    foreach ($this->invCode as $sourceCode) {
//                        $instockInv = 0;
//                        if ($this->isProductsInStockAll($wrehs, $skus)) {
//                            $instockInv = 1;
//                            echo "instockInv ".$instockInv."<br>";
//                            break;
//                        }
//                        echo "foreeach invCode ".$instockInv."<br>";
//                    }

                    //check if accessories group
                    $is_acce = false; //do check for acce - clint may 7 2024
//                    foreach ($order->getAllVisibleItems() as $item) {
//                        /* @var $item \Magento\Sales\Model\Order\Item */
//
//                        echo $item->getSku()."<br>";
//                        $stockgroup = $item->getProduct()->getCustomAttribute('stock_group');
//                        if(is_null($stockgroup))
//                        {
//
//                        }
//                        else
//                        {
//                            $accgroup = $stockgroup->getValue();
//                            if(!in_array($stockgroup,$this->acceGroup)){
//                                $is_acce = false; //order has one that is not accessories
//                                break;
//                            }
//                        }
//
//                    }

                    //set ['set-on-status'] to B if no stock. if BT payment method, check if not fraud
                    //check if braintree and fraud
                    //check if all product has stock
                    $delivery = $order->getShippingDescription();
                    if($payment_type == 'BT')
                    {
                        if ($order->getStatus() != 'fraud')
                        {

                            if($instockInv == 1)
                            {
                                $data['sales-order']['header']['on-hold-reason-code'] = "WP";
                                $data['sales-order']['header']['set-on-status'] = "H";

                                if($delivery == "Pick Up in Store - Click and Collect Shipping")
                                {
                                    $data['sales-order']['header']['on-hold-reason-code'] = "";
                                    $data['sales-order']['header']['set-on-status'] = "P";

                                }
//                                if($grandTotal < 200)
//                                {
//                                    $data['sales-order']['header']['on-hold-reason-code'] = "";
//                                    $data['sales-order']['header']['set-on-status'] = "P";
//                                }
//                                else //$grandTotal >= 200
//                                {
//                                    if($is_acce) //greater than 200 and is accessories
//                                    {
//                                        $data['sales-order']['header']['on-hold-reason-code'] = "";
//                                        $data['sales-order']['header']['set-on-status'] = "P";
//                                    }
//                                    else
//                                    {
//                                        $data['sales-order']['header']['on-hold-reason-code'] = "WP";
//                                        $data['sales-order']['header']['set-on-status'] = "H";
//                                    }
//                                }
                            }
                            else
                            {

                                if($delivery == "Pick Up in Store - Click and Collect Shipping")
                                {
                                    $data['sales-order']['header']['on-hold-reason-code'] = "WS";
                                    $data['sales-order']['header']['set-on-status'] = "H";

                                }
                                else
                                {
                                    $data['sales-order']['header']['on-hold-reason-code'] = "";
                                    $data['sales-order']['header']['set-on-status'] = "B";
                                }
                            }



                        }
                        else
                        {
                            $data['sales-order']['header']['on-hold-reason-code'] = "WF";
                            $data['sales-order']['header']['set-on-status'] = "H";
                        }

                    }
                    elseif($payment_type == 'Y')
                    {
                        $data['sales-order']['header']['on-hold-reason-code'] = "WP";
                        $data['sales-order']['header']['set-on-status'] = "H";
                    }
                    else
                    {
                        $delivery = "";
                        if (strpos($orderId, 'REEB') !== false) {
                            $delivery = "";
                        }
                        else
                        {
                            $delivery = $order->getShippingDescription();
                        }

                        if($instockInv == 1)
                        {

                            if($delivery == "Next Day Delivery")
                            {
                                $data['sales-order']['header']['on-hold-reason-code'] = "";
                                $data['sales-order']['header']['set-on-status'] = "P";
                            }
                            elseif($grandTotal < 200)
                            {
                                $data['sales-order']['header']['on-hold-reason-code'] = "";
                                $data['sales-order']['header']['set-on-status'] = "P";
                            }
                            else
                            {
                                if($is_acce) //greater than 200 and is accessories
                                {
                                    $data['sales-order']['header']['on-hold-reason-code'] = "";
                                    $data['sales-order']['header']['set-on-status'] = "P";
                                }
                                else
                                {
                                    $data['sales-order']['header']['on-hold-reason-code'] = "WP";
                                    $data['sales-order']['header']['set-on-status'] = "H";

                                    if($delivery == "Pick Up in Store - Click and Collect Shipping")
                                    {
                                        $data['sales-order']['header']['on-hold-reason-code'] = "";
                                        $data['sales-order']['header']['set-on-status'] = "P";

                                    }
                                }
                            }
                        }
                        else
                        {
                            if($delivery == "Pick Up in Store - Click and Collect Shipping")
                            {
                                $data['sales-order']['header']['on-hold-reason-code'] = "WS";
                                $data['sales-order']['header']['set-on-status'] = "H";

                            }
                            else
                            {
                                $data['sales-order']['header']['on-hold-reason-code'] = "";
                                $data['sales-order']['header']['set-on-status'] = "B";
                            }
                        }
                    }
                }

                if($method == "braintree_googlepay" || $method == "braintree_applepay" || $method == "latipay" || $method == "banktransfer")
                {
                    $data['sales-order']['header']['on-hold-reason-code'] = "WP";
                    $data['sales-order']['header']['set-on-status'] = "H";
                }

                if($payment_type == 'LP')
                {
                    $data['sales-order']['header']['on-hold-reason-code'] = "WP";
                    $data['sales-order']['header']['set-on-status'] = "H";
                    if($state == 'pending' && $test)
                    {
                        echo "latipay pending";
                        continue;
                    }
            }

                if($payment_type == 'VI')
                {
                    $data['sales-order']['header']['on-hold-reason-code'] = "WP";
                    $data['sales-order']['header']['set-on-status'] = "H";
                }

            }

            $data['sales-order']['header']['so-part-shipment-allowed'] = "N";

            //echo "<br> WH - ".$data['sales-order']['header']['warehouse'];

            $grandTotal = round($grandTotal, 2);
            $data['sales-order']['header']['order-total-inc-tax'] = $grandTotal;

            $street = "";
            if(!(is_null($address->getStreet())))
            {
                $strt = $address->getStreet();
                if(is_array($strt))
                {
                    $street = implode(",", $strt);
                }
                else
                {
                    $street = $strt;
                }
            }


            $city = $address->getCity();
            $region = $address->getRegion();
            $postcode = $address->getPostcode();
            $countrycode = $address->getCountryId();
            $phone = $address->getTelephone();
            $mobile = $address->getMobile();
            $company = $address->getCompany();
            $unitNumber = $address->getUnitNumber();
            if(!empty($unitNumber))
            {
                $unitNumber = str_replace("unit_number"," ",$unitNumber);
            }

            $data['sales-order']['header']['billing-address']['line-1'] = $company;
            $data['sales-order']['header']['billing-address']['line-2'] = $unitNumber." ".$street;
            $data['sales-order']['header']['billing-address']['line-3'] = $city;
            $data['sales-order']['header']['billing-address']['line-4'] = $region;
            $data['sales-order']['header']['billing-address']['line-6'] = $countryName;
            $data['sales-order']['header']['billing-address']['postcode'] = $postcode;
            $data['sales-order']['header']['billing-address']['country-code'] = $countrycode;
            $data['sales-order']['header']['billing-address']['phone'] = $phone;
            $data['sales-order']['header']['billing-address']['mobile'] = $mobile;

            if (strpos($orderId, 'REEB') !== false) {
                $delivery = "";
            }
            else
            {
                $delivery = $order->getShippingDescription();
            }

            $shipaddress = $order->getShippingAddress();
            $shipstrt = $shipaddress->getStreet();
            if(is_array($shipstrt))
            {
                $shipstreet = implode(",", $shipstrt);

            }

            $shipcity = $shipaddress->getCity();
            $shipregion = $shipaddress->getRegion();
            $shippostcode = $shipaddress->getPostcode();
            $shipcountrycode = $shipaddress->getCountryId();
            $shipphone = $shipaddress->getTelephone();
            $shipmobile = $shipaddress->getMobile();
            $shipcompany = $shipaddress->getCompany();
            $shipUnitNumber = $shipaddress->getUnitNumber();
            if(!empty($shipUnitNumber))
            {
                $shipUnitNumber = str_replace("unit_number"," ",$shipUnitNumber);
                $shipUnitNumber = preg_replace('/[^A-Za-z0-9. -]/', '', $shipUnitNumber);
            }

            if($delivery == "Pick Up in Store - Click and Collect Shipping")
            {
                $shipcompany = 'Click and Collect';

            }
            else if($rep == "WESTFIELD")
            {
                $shipcompany = 'Click and Collect';
            }

            if($delivery == "Next Day Delivery")
            {
                if($payment_type == 'LP' || $payment_type == 'BT')
                {
                    $data['sales-order']['header']['on-hold-reason-code'] = "WP";
                    $data['sales-order']['header']['set-on-status'] = "H";
                }

            }

            $contactname = preg_replace('/[^A-Za-z0-9. -]/', '', $contactname);

            //$shipstreet = preg_replace('/[^A-Za-z0-9. -]/', '', $shipstreet);
            $data['sales-order']['header']['delivery-address']['line-1'] = $contactname;
            $data['sales-order']['header']['delivery-address']['line-2'] = $shipcompany;
            $data['sales-order']['header']['delivery-address']['line-3'] = $shipUnitNumber." ".$shipstreet;
            $data['sales-order']['header']['delivery-address']['line-4'] = $shipcity;
            $data['sales-order']['header']['delivery-address']['line-5'] = $shipregion;
            $data['sales-order']['header']['delivery-address']['line-6'] = $countryName;
            $data['sales-order']['header']['delivery-address']['postcode'] = $shippostcode;
            $data['sales-order']['header']['delivery-address']['country-code'] = $shipcountrycode;
            $data['sales-order']['header']['delivery-address']['phone'] = $shipphone;
            $data['sales-order']['header']['delivery-address']['mobile'] = $shipmobile;


            $payment_reference = $paymentInstance->getLastTransId();
            if($test)
            {
                $latdata = $paymentInstance->getAdditionalInformation();
                var_dump($latdata);

            }
            if($method == 'latipay')
            {
                $tosync = 0;
                $status_history = $order->getStatusHistories();
                foreach ($status_history as $status) {
                    //echo $status->getStatusLabel() . "- " . $status->getComment() . " (on " . $status->getCreatedAt() . ")\n";
                    $comment = $status->getComment();
                    if(!empty($comment))
                    {
                        $myjson = str_replace("Latipay Response :", "",$comment);
                        //echo $myjson ."\n";
                        $myarray = json_decode($myjson, true);
                        var_dump($myarray);
                        if(isset($myarray['status']))
                        {
                            $latistatus = $myarray['status'];
                            echo "latistatus ".$latistatus."<br/>";
                            if($latistatus == 'paid')
                            {
                                echo "true <br/>";
                                $tosync = 1;
                            }
//                            else {
//                                echo "false <br/>";
//                                $tosync = 0;
//                            }
                        }
//                        else
//                        {
//                            $tosync = 0;
//                        }

                    }

                }

                echo "to sync ".$tosync."<br/>";
                if(!$tosync)
                {
                    continue;
                }

            }

//            if (empty($payment_reference) && ($method == 'latipay')) {
//                //$payment_reference = $paymentInstance->getAdditionalInformation('klarna_order_id');
//                //if (empty($payment_reference)){
//                if(!$test)
//                {
//                    continue;
//                }
//                //}
//
//            }

            //ebay
            if (($method == 'm2epropayment')) {
                if($paymentInstance->getAdditionalInformation('component_mode') == 'ebay')
                {
                    $payment_reference = $paymentInstance->getAdditionalInformation('channel_order_id');
                }
                else if (empty($payment_reference))
                {
                    $payment_reference = $paymentInstance->getAdditionalInformation('channel_order_id');
                }
            }

            if (($payment_type == 'ZM')) {

                $payment_reference = $paymentInstance->getAdditionalInformation('receipt_number');
//               if($payment_reference == '')
//               {
//                   $payment_reference = $paymentInstance->getAdditionalInformation('zip_checkout_id');
//               }
            }

            //paypal express fix
            if (($payment_type == 'PX')) {

                $payment_status = $paymentInstance->getAdditionalInformation('paypal_payment_status');
                if($payment_status == 'pending')
                {
                    echo "pending paypal <br />";
                    continue;
                }
            }

            if (($payment_type == 'BT')) {

                $liabilityShifted = $paymentInstance->getAdditionalInformation('liabilityShifted');
                echo "liabilityShifted " .$liabilityShifted;
                if($liabilityShifted != 'Yes')
                {
                    $data['sales-order']['header']['on-hold-reason-code'] = "WP";
                    $data['sales-order']['header']['set-on-status'] = "H";
                }
            }

            //work around for IR orders coming as H
            if($payment_type == 'H')
            {
                if (strpos($orderId, 'CATCH') !== false) {
                    $payment_type ="CA";
                    $catchRef = $orderId;
                    $catchRef = str_replace("CATCH","",$catchRef);
                    $payment_reference = $catchRef;
                }
                else if (strpos($orderId, 'MYD') !== false) {
                    $payment_type ="MD";
                    $catchRef = $orderId;
                    $catchRef = str_replace("MYD","",$catchRef);
                    $payment_reference = $catchRef;
                }
                else if (strpos($orderId, 'AM') !== false) {
                    $payment_type ="AM";
                    $catchRef = $orderId;
                    $catchRef = str_replace("AM","",$catchRef);
                    $payment_reference = $catchRef;
                }
                else if (strpos($orderId, 'WD') !== false) {
                    $payment_type ="WD";
                    $catchRef = $orderId;
                    $catchRef = str_replace("WD","",$catchRef);
                    $payment_reference = $catchRef;
                }
                else if (strpos($orderId, 'EB') !== false) {
                    $payment_type ="EB";
                    $catchRef = $orderId;
                    $catchRef = str_replace("EB","",$catchRef);
                    $payment_reference = $catchRef;

                    if (strpos($orderId, 'REEB') !== false) {
                        $payment_type ="REEB";
                        $catchRef = $orderId;
                        $catchRef = str_replace("REEB","",$catchRef);
                        $payment_reference = $catchRef;
                    }

                }
                else if (strpos($orderId, 'WW') !== false) {
                    $payment_type ="WW";
                    $catchRef = $orderId;
                    $catchRef = str_replace("WW","",$catchRef);
                    $payment_reference = $catchRef;
                }
            }

            if(($is_am_order) && ($payment_type == "EB")){
                $payment_type = "AM";
            }

            $withpaymentref = true;
            if(($payment_type == "Y"))
            {
                $withpaymentref = false;
            }
            if(($payment_type == "H"))
            {
                $withpaymentref = false;
            }
            if(($payment_type == "VI"))
            {
                $withpaymentref = false;
            }
            //gift cards
            $withGC = false;
            $gift_amount = $order->getGiftCardsAmount();
            echo "gift_amount ".$gift_amount."<br/>";

            if($gift_amount > 0)
            {
                $withGC = true;
                $gift_amount = round($gift_amount, 2);
                $gc_data = $order->getGiftCards();
                $arr = explode(",",$gc_data);
                $gc_ref = explode(":", $arr[1]);
                $gc_reference = $gc_ref[1];
                if($test)
                {
                    echo "gc ref " .$gc_reference;
                }

                $data['sales-order']['header']['payment-details']['payment-detail'][0]['payment-type'] = "VI";
                $data['sales-order']['header']['payment-details']['payment-detail'][0]['payment-reference'] = $gc_reference;
                $data['sales-order']['header']['payment-details']['payment-detail'][0]['amount-tendered'] = $gift_amount;
            }

            $amount_tendered = $order->getBaseGrandTotal();
            if($modifygrandtotal)
            {
                $amount_tendered = $amount_tendered + $surcharge;
                if($disregardshipping)
                {
                    $amount_tendered = $amount_tendered - 9.9;
                }
            }
            //pao's order 001313994-1 001313991-1
            if($orderId == '001901158')
            {
                $amount_tendered = 1986.10;
            }


            $amount_tendered = round($amount_tendered, 2);
            echo "amount_tendered ".$amount_tendered."<br/>";
            if((!$is_am_fba))
            {
                if($withpaymentref)
                {
                    if($withGC)
                    {
                        $data['sales-order']['header']['payment-details']['payment-detail'][1]['payment-type'] = $payment_type;
                        $data['sales-order']['header']['payment-details']['payment-detail'][1]['payment-reference'] = $payment_reference." ".$cc;
                        $data['sales-order']['header']['payment-details']['payment-detail'][1]['amount-tendered'] = $amount_tendered;
                    }
                    else
                    {
                        $data['sales-order']['header']['payment-details']['payment-detail']['payment-type'] = $payment_type;
                        $data['sales-order']['header']['payment-details']['payment-detail']['payment-reference'] = $payment_reference." ".$cc;
                        $data['sales-order']['header']['payment-details']['payment-detail']['amount-tendered'] = $amount_tendered;
                    }
                }

            }

            $sellerdata['sales-order']['header']['order-total-inc-tax'] = $amount_tendered;

            //CUSTOM DATA
            $qffNumber = $order->getQffNumber();
            $qffLastname = $order->getQffLastname();


            echo "surcharge - " .$surcharge;

            if (!empty($qffNumber) && !empty($qffLastname)) {
                $data['sales-order']['header']['custom-data']['data'][0]['key'] = 'QFF';
                $data['sales-order']['header']['custom-data']['data'][0]['value'] = $qffNumber;
                $data['sales-order']['header']['custom-data']['data'][1]['key'] = 'QFFSURNAME';
                $data['sales-order']['header']['custom-data']['data'][1]['value'] = $qffLastname;
            }
            else
            {
                $data['sales-order']['header']['custom-data']['data'][0]['key'] = 'QFF';
                $data['sales-order']['header']['custom-data']['data'][0]['value'] = NULL;
                $data['sales-order']['header']['custom-data']['data'][1]['key'] = 'QFFSURNAME';
                $data['sales-order']['header']['custom-data']['data'][1]['value'] = NULL;
            }

            $data['sales-order']['header']['custom-data']['data'][2]['key'] = 'magento-order-number';
            $data['sales-order']['header']['custom-data']['data'][2]['value'] = $orderId;

            $data['sales-order']['header']['custom-data']['data'][3]['key'] = 'email';
            $data['sales-order']['header']['custom-data']['data'][3]['value'] = $customerEmail;

            if (!$order->getCustomerIsGuest()) {
                $customerRep = $this->customerRepository->getById($order->getCustomerId());
                $customerGroupId = $customerRep->getGroupId();
                if($customerGroupId == 10)
                {
                    $data['sales-order']['header']['custom-data']['data'][4]['key'] = 'marketing-flag';
                    $data['sales-order']['header']['custom-data']['data'][4]['value'] = 'CLUB';
                }
            }


            //for coupon
            $coupon = $order->getCouponCode();
            $couponDiscount = ((double) $order->getBaseDiscountAmount());

            //product lines
            // for redeploy
            $x = 0;
            $gotDigiProducts = false;
            foreach ($order->getAllVisibleItems() as $item)
            {
                /* @var $item \Magento\Sales\Model\Order\Item */

                $skus = array();
                $productSku = "";
                $digiProtect = "";
                $price = (double) $item->getBasePriceInclTax();
                $qty = (double) $item->getQtyOrdered();
                $discount = (double) $item->getDiscountAmount();
                echo "discount - " .$discount ." <br/>";
                $todiscount = $price * $qty;
                $total = ($price * $qty) - $discount;
                $discperc = 0;
                if($price > 0)
                {
                    $discperc = ($discount / $todiscount) * 100;
                }
                echo "discperc - " .$discperc ." <br/>";
                if($coupon != "")
                {
                    $discount = 0;
                    $discperc = 0;
                }
                $digiProtectPrice = 0;
                $digiProtectQty = 0;
                $digiProtectdiscount = 0;
                $digiProtectTotal = 0;


                $sku = $item->getSku();
                $productDetails = $this->productFactory->create();

                echo "SKU - " .$sku."<br/>";
                if(strpos($sku, 'mp-') !== false)
                {
                    //check seller here
                    $productDetails->load($productDetails->getIdBySku($sku));
                    $sell = $productDetails->getMarketplacerSeller();
                    echo "is MP - " .$sku." - seller : ".$sell."<br/>";
                    if($this->currentseller == $sell)
                    {
                        continue;
                    }
                    else
                    {
                        if($this->syncedseller == $sell)
                        {
                            continue;
                        }
                        else
                        {
                            $this->currentseller = $sell;
                            $newaccount = $this->orderPostBySeller($orderId, $date, $size, $page, $test, $sell, $newaccount);
                        }

                    }

                    continue;

                }
                else if(strpos($sku, '-') !== false)
                {
                    echo "is digiprotect - " .$sku."<br/>";
                    $gotDigiProducts = true;
                    $skus = explode('-', $sku);
                    $productSku = $skus[0];
                    $digiProtect = $skus[1];

//                    $price = (double) $item->getBasePriceInclTax();
//                    $orig = (double) $item->getOriginalPrice();
//                    $digiProtectPrice = $price - $orig;
//                    $digiProtectQty = (double) $item->getQtyOrdered();
//                    $digiProtectdiscount = (double) $item->getDiscountAmount();
//                    if($coupon != "")
//                    {
//                        $digiProtectdiscount = 0;
//                    }
//                    $digiProtectTotal = ($digiProtectPrice * $digiProtectQty) - $digiProtectdiscount;

                    $productDigiprot = $this->productFactory->create();
                    $productPriceBySku = $productDigiprot->loadByAttribute('sku', $digiProtect)->getPrice();
                    $digiProtectPrice = $productPriceBySku;
                    $digiProtectQty = (double) $item->getQtyOrdered();
                    $digiProtectdiscount = 0;
                    if($coupon != "")
                    {
                        $digiProtectdiscount = 0;
                    }
                    $digiProtectTotal = ($digiProtectPrice * $digiProtectQty) - $digiProtectdiscount;
                    $price = $price - $digiProtectTotal;
                    echo "digiprotect price ".$digiProtectPrice."<br/>";
                    $data['sales-order']['detail']['line'][$x]['line-type'] = 'SN';
                }
                else
                {
                    echo "is normal products - " .$sku."<br/>";
                    $gotDigiProducts = true;
                    $productSku = $sku;
                    $data['sales-order']['detail']['line'][$x]['line-type'] = 'SN';

                }

                echo " got digi product ".$gotDigiProducts."<br/>";
                if($gotDigiProducts)
                {
                    $data['sales-order']['detail']['line'][$x]['stock-code'] = $productSku;
                    $data['sales-order']['detail']['line'][$x]['description'] = $item->getName();
                    $data['sales-order']['detail']['line'][$x]['unit-price-inc-tax'] = $price;


                    if($data['sales-order']['header']['set-on-status'] == "B")
                    {
                        $data['sales-order']['detail']['line'][$x]['ordered'] = $qty;
                        $data['sales-order']['detail']['line'][$x]['shipped'] = 0;
                        $data['sales-order']['detail']['line'][$x]['backordered'] = $qty;
                    }
                    else
                    {
                        //if instock shipped = qty backordered = 0, if out of stock shipped = 0 backordered = qty
                        if($data['sales-order']['header']['on-hold-reason-code'] == "WS")
                        {
                            $data['sales-order']['detail']['line'][$x]['ordered'] = $qty;
                            $data['sales-order']['detail']['line'][$x]['shipped'] = 0;
                            $data['sales-order']['detail']['line'][$x]['backordered'] = $qty;
                        }
                        else
                        {
                            $data['sales-order']['detail']['line'][$x]['ordered'] = $qty;
                            $data['sales-order']['detail']['line'][$x]['shipped'] = $qty;
                            $data['sales-order']['detail']['line'][$x]['backordered'] = 0;
                        }
                    }


                    $data['sales-order']['detail']['line'][$x]['sol-disc-rate'] = $discperc;
                    $data['sales-order']['detail']['line'][$x]['sol-line-total-inc-tax'] = $total;
                    $x++;

                    if(!empty($digiProtect))
                    {
                        $price = (double) $item->getBasePriceInclTax();
                        $qty = (double) $item->getQtyOrdered();
                        $discount = (double) $item->getDiscountAmount();
                        $total = ($price * $qty) - $discount;
                        $data['sales-order']['detail']['line'][$x]['line-type'] = 'SN';
                        $data['sales-order']['detail']['line'][$x]['stock-code'] = $digiProtect;
                        $data['sales-order']['detail']['line'][$x]['description'] = "digiProtect";
                        $data['sales-order']['detail']['line'][$x]['unit-price-inc-tax'] = $digiProtectPrice;
                        $data['sales-order']['detail']['line'][$x]['ordered'] = $digiProtectQty;
                        $data['sales-order']['detail']['line'][$x]['shipped'] = 0;
                        $data['sales-order']['detail']['line'][$x]['backordered'] = $digiProtectQty;
                        $data['sales-order']['detail']['line'][$x]['sol-disc-rate'] = $digiProtectdiscount;
                        $data['sales-order']['detail']['line'][$x]['sol-line-total-inc-tax'] = $digiProtectTotal;
                        $x++;
                    }
                }

            } //end of product line

            if($newaccount != "")
            {
                $data['sales-order']['header']['account'] = $newaccount;
            }

            if($gotDigiProducts)
            {
                //surcharge clint 01-20-23
                if($surcharge != "0.0000")
                {
                    $data['sales-order']['detail']['line'][$x]['line-type'] = 'SC';
                    $data['sales-order']['detail']['line'][$x]['description'] = "Surcharge";
                    $data['sales-order']['detail']['line'][$x]['unit-price-inc-tax'] = $surcharge;
                    $data['sales-order']['detail']['line'][$x]['ordered'] = 1;
                    $data['sales-order']['detail']['line'][$x]['shipped'] = 1;
                    $data['sales-order']['detail']['line'][$x]['sol-disc-rate'] = 0;
                    $data['sales-order']['detail']['line'][$x]['sol-chg-type'] = "C3";
                    $data['sales-order']['detail']['line'][$x]['sol-line-total-inc-tax'] = $surcharge;
                    $x++; // for shipping counter
                }

                if($coupon != "")
                {
                    $data['sales-order']['detail']['line'][$x]['line-type'] = 'SC';
                    $data['sales-order']['detail']['line'][$x]['description'] = $coupon;
                    $data['sales-order']['detail']['line'][$x]['unit-price-inc-tax'] = $couponDiscount;
                    $data['sales-order']['detail']['line'][$x]['ordered'] = 1;
                    $data['sales-order']['detail']['line'][$x]['shipped'] = 1;
                    $data['sales-order']['detail']['line'][$x]['sol-disc-rate'] = 0;
                    $data['sales-order']['detail']['line'][$x]['sol-chg-type'] = "C5";
                    $data['sales-order']['detail']['line'][$x]['sol-line-total-inc-tax'] = $couponDiscount;
                    $x++; // for shipping counter
                }


                $shippingprice = (double) $order->getShippingAmount();
                $shippingDesc = $order->getShippingDescription();
                if (strpos($shippingDesc, '|') !== false) {
                    $marketplacesShipping = explode('|', $shippingDesc);
                    $shippingDesc = $marketplacesShipping[1];
                }
                if($shippingDesc == "Express - (1 to 3 Days)")
                {
                    $shippingDesc = "Australia Post – express";
                }
                else if($shippingDesc == "Standard - (4 to 7 Days)")
                {
                    $shippingDesc = "Australia Post – eParcel";
                }
                else if($rep == 'WESTFIELD')
                {
                    $shippingDesc = "Click and Collect";
                }
                else if($shippingDesc == "AU_ExpressPostParcelSignature")
                {
                    $shippingDesc = "Australia Post – express";
                }
                else if($shippingDesc == "AU_RegularParcelWithTrackingAndSignature")
                {
                    $shippingDesc = "Australia Post – eParcel";
                }
                //shipping details clint Mar 3 23
                if($disregardshipping)
                {
                    $shippingprice = 0;
                }
                $data['sales-order']['detail']['line'][$x]['line-type'] = 'SC';
                $data['sales-order']['detail']['line'][$x]['description'] = $shippingDesc;
                $data['sales-order']['detail']['line'][$x]['unit-price-inc-tax'] = $shippingprice;
                $data['sales-order']['detail']['line'][$x]['ordered'] = 1;
                $data['sales-order']['detail']['line'][$x]['shipped'] = 1;
                $data['sales-order']['detail']['line'][$x]['sol-disc-rate'] = 0;
                $data['sales-order']['detail']['line'][$x]['sol-chg-type'] = "C1";
                $data['sales-order']['detail']['line'][$x]['sol-line-total-inc-tax'] = $shippingprice;

                //if($test)
                //{
                var_dump($data['sales-order']);
                //}
                //create xml of order data here
                //$this->logger->info('Pronto Order Sync Data - ',$data['sales-order']);
                $xml = \Digidirect\AI\Model\Lib\Adapter\Import\Xml::assocToXml($data, 'sales-orders');

                //TEST
                //$url = 'https://digi-pronto.abtonline.com.au:8083/rest/abtws/sales?call-type=create_orders'; //TEST

                //LIVE - port :8084
                $url = 'https://digi-pronto.abtonline.com.au:8084/rest/abtws/sales?call-type=create_orders';


                if(!$test)
                {
                    $this->curl->addHeader("Content-Type", "application/xml");
                    $this->curl->addHeader("Accept", "application/json");
                    $this->curl->addHeader("compcode", "DIG"); //live
                    $this->curl->addHeader("user", "ewaveapi");
                    $this->curl->addHeader("token", "904241bdbf10efa9");
                    //
//                    $this->curl->addHeader("compcode", "UA1"); //test
//                    $this->curl->addHeader("user", "clint.mercado");
//                    $this->curl->addHeader("token", "849cd5080faff5ce");

                    $this->curl->setOption(CURLOPT_SSL_VERIFYHOST,false);
                    $this->curl->setOption(CURLOPT_SSL_VERIFYPEER,false);
                    $this->curl->post($url, $xml);

                    $result = $this->curl->getBody();

                    $json = $this->jsonSerializer->unserialize($result);

                    if(isset($json['response']['status']) && ($json['response']['status'] == 'FAIL'))
                    {
                        $msg =  $json['response']['message'];
                        echo "<br> fail - ".$msg."<br>";
                        $order->setData('pronto_order_number',$msg);
                        $order->save();
                        //$this->logger->error('Pronto Order Sync', array('info' => $msg));

                    }
                    else if (isset($json['sales-orders']['response']['status']) && ($json['sales-orders']['response']['status'] == 'failed')) {
                        $msg =  $json['sales-orders']['response']['message'];
                        echo "<br> fail - ".$msg."<br>";
                        $order->setData('pronto_order_number',$msg);
                        $order->save();
                        //$this->logger->error('Pronto Order Sync', array('info' => $msg));

                    }
                    else {

                        $pronto = $json['sales-orders']['sales-order']['order-no'];
                        $invoiceno = $json['sales-orders']['sales-order']['invoice-no'];
                        $prontostatus = $json['sales-orders']['sales-order']['order-status-code'];
                        $order->setData('pronto_order_number',$pronto);
                        $order->setData('pronto_status_code',$prontostatus);
                        $order->save();


                        //$this->logger->info('Pronto Order Sync ', $json['sales-orders']['sales-order']);
                        var_dump($json['sales-orders']['sales-order']);
                        $account = $json['sales-orders']['sales-order']['account'];
                        if (!empty($account) && !$order->getCustomerIsGuest()) {
                            $customer = $this->customerRepository->getById($order->getCustomerId());
                            $customer->setData('pronto_account_id', $account);
                            $customer->setCustomAttribute('pronto_account_id', $account);
                            $this->customerRepository->save($customer);

                        }
                        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
                        $invoice = $order->getInvoiceCollection()->getFirstItem();
                        $this->incrementIdUpdater->update($invoice, $invoiceno);

                    }
                }
            }

            if($counter >= $size)
            {
                return true; //return after 2 orders
            }

        }

    }

    public function orderPostBySeller($orderId, $date, $size, $page, $test, $seller, $newaccount)
    {

        settype($test,"integer");
        settype($size,"integer");
        //get order data
        $orders = $this->getTestOrderCollection($orderId, $date, $size, $page);
        $counter = 0;
        foreach ($orders as $order)
        {
            $sellerdata = array();
            $counter++;
            $state = $order->getState();
            /* @var $order \Magento\Sales\Model\Order */

            if(!$test)
            {
                if ($order->getState() == 'canceled') {
                    continue;
                }

                if ($order->getState() == 'pending') {
                    continue;
                }
            }

            $prontoOrderNumber = $order->getData('pronto_order_number');
            /*if(is_numeric($prontoOrderNumber))
            {
                echo "Has pronto ". $prontoOrderNumber."<br/>";
                if(!$test)
                {
                    continue;
                }

            }*/

            $orderId = $order->getIncrementId();
            $entityId = $order->getId();
            $this->logger->info('Pronto Order Sync - '.$orderId);
            $isMarketPlace = false;
            //Amazon Logic
            $wrehs = 'DIMA';//$this->getWarehouse($order); // or use DIMA too?
            $territory = "DIMA";
            $rep = $this->getRep($order);

            $accountname = $this->getAccountName($order);
            if($newaccount != "")
            {
                $account = $newaccount;
            }
            else
            {
                $account = $this->getAccount($order);
            }

            $address = $order->getBillingAddress();
            $countrycode = $address->getCountryId();
            $countryName = "";
            if(isset($countrycode))
            {
                $country = $this->countryFactory->create()->loadByCode($countrycode);
                if ($country) {
                    $countryName = $country->getName();
                }
            }
            echo "seller data".$seller."<br/>";

            $directToWhse = false;

            $contactname = $accountname;
            //check pronto if customer has an account.
            //if not, create customer account to pronto
            $created = $order->getCreatedAt();
            $created = $this->timezone->date(new \DateTime($created));
            $orderdate = $created->format('Y-m-d');

            $customertype = "WG";
            if (!empty($account) && !$order->getCustomerIsGuest()) {
                $customertype = "WA";
            }

            //comment to redeploy

            $customerEmail = $order->getCustomerEmail();
            $sellerdata['sales-order']['header']['accountname'] = $accountname;
            $sellerdata['sales-order']['header']['account'] = $account;
            $sellerdata['sales-order']['header']['order-date'] = $orderdate;
            $sellerdata['sales-order']['header']['warehouse'] = $wrehs;
            $sellerdata['sales-order']['header']['customer-type'] = $customertype;
            $sellerdata['sales-order']['header']['so-cust-type'] = $customertype;
            $sellerdata['sales-order']['header']['territory'] = $territory;
            $sellerdata['sales-order']['header']['rep'] = $rep;
            $sellerdata['sales-order']['header']['contactname'] = $contactname;
            $sellerdata['sales-order']['header']['email'] = $customerEmail;
            $sellerdata['sales-order']['header']['reference'] = $entityId;

            //changes
            $paymentInstance = $order->getPayment();

            //payment details

            //$methodInst = $paymentInstance->getMethodInstance();
            $method = $paymentInstance->getMethod();

            $payment_type = $this->getPaymentType($paymentInstance);
            echo "Payment type ".$payment_type."<br/>";
            $cc = "";

            $grandTotal = (double) $order->getBaseGrandTotal();
            echo "grandTotal - ".$grandTotal."<br/>";
            $subTotal = (double) $order->getBaseSubtotalInclTax();
            $tax = (double) $order->getBaseTaxAmount();
            $shipping = (double) $order->getBaseShippingInclTax();

            //Workaround clint Mar 3 23.
            $disregardshipping = false;
            $modifygrandtotal = false;
            $surcharge = $order->getPaymentFee();


            if($payment_type == 'BT')
            {
                $cc = $paymentInstance->getCcType();
            }
            //redeploy
            $sellerdata['sales-order']['header']['on-hold-reason-code'] = "WS";
            $sellerdata['sales-order']['header']['set-on-status'] = "H";
//                WF – Web Fraud  ( this would be orders flagged in BT or other platforms as needing a fraud check )
//                WS – Web Stock Shortage ( this would be an order placed on hold for a stock shortage reason. For example a marketplace order where there is no stock in 3WHS )
//                WP – Web Payment ( this would be for orders we cannot process because we need to apply payment example would be direct deposit but maybe also Studio 19 ?? )


            $sellerdata['sales-order']['header']['so-part-shipment-allowed'] = "N";

            //echo "<br> WH - ".$data['sales-order']['header']['warehouse'];

            $grandTotal = round($grandTotal, 2);
            //$sellerdata['sales-order']['header']['order-total-inc-tax'] = $grandTotal;

            $strt = $address->getStreet();
            if(is_array($strt))
            {
                $street = implode(",", $strt);
            }
            else
            {
                $street = $strt;
            }
            $city = $address->getCity();
            $region = $address->getRegion();
            $postcode = $address->getPostcode();
            $countrycode = $address->getCountryId();
            $phone = $address->getTelephone();
            $mobile = $address->getMobile();
            $company = $address->getCompany();
            $unitNumber = $address->getUnitNumber();
            if(!empty($unitNumber))
            {
                $unitNumber = str_replace("unit_number"," ",$unitNumber);
            }

            $sellerdata['sales-order']['header']['billing-address']['line-1'] = $company;
            $sellerdata['sales-order']['header']['billing-address']['line-2'] = $unitNumber." ".$street;
            $sellerdata['sales-order']['header']['billing-address']['line-3'] = $city;
            $sellerdata['sales-order']['header']['billing-address']['line-4'] = $region;
            $sellerdata['sales-order']['header']['billing-address']['line-6'] = $countryName;
            $sellerdata['sales-order']['header']['billing-address']['postcode'] = $postcode;
            $sellerdata['sales-order']['header']['billing-address']['country-code'] = $countrycode;
            $sellerdata['sales-order']['header']['billing-address']['phone'] = $phone;
            $sellerdata['sales-order']['header']['billing-address']['mobile'] = $mobile;

            $delivery = $order->getShippingDescription();

            $shipaddress = $order->getShippingAddress();
            $shipstrt = $shipaddress->getStreet();
            if(is_array($shipstrt))
            {
                $shipstreet = implode(",", $shipstrt);

            }

            $shipcity = $shipaddress->getCity();
            $shipregion = $shipaddress->getRegion();
            $shippostcode = $shipaddress->getPostcode();
            $shipcountrycode = $shipaddress->getCountryId();
            $shipphone = $shipaddress->getTelephone();
            $shipmobile = $shipaddress->getMobile();
            $shipcompany = $shipaddress->getCompany();
            $shipUnitNumber = $shipaddress->getUnitNumber();
            if(!empty($shipUnitNumber))
            {
                $shipUnitNumber = str_replace("unit_number"," ",$shipUnitNumber);
            }


            $sellerdata['sales-order']['header']['delivery-address']['line-1'] = $contactname;
            $sellerdata['sales-order']['header']['delivery-address']['line-2'] = $shipcompany;
            $sellerdata['sales-order']['header']['delivery-address']['line-3'] = $shipUnitNumber." ".$shipstreet;
            $sellerdata['sales-order']['header']['delivery-address']['line-4'] = $shipcity;
            $sellerdata['sales-order']['header']['delivery-address']['line-5'] = $shipregion;
            $sellerdata['sales-order']['header']['delivery-address']['line-6'] = $countryName;
            $sellerdata['sales-order']['header']['delivery-address']['postcode'] = $shippostcode;
            $sellerdata['sales-order']['header']['delivery-address']['country-code'] = $shipcountrycode;
            $sellerdata['sales-order']['header']['delivery-address']['phone'] = $shipphone;
            $sellerdata['sales-order']['header']['delivery-address']['mobile'] = $shipmobile;


            $payment_reference = $paymentInstance->getLastTransId();
            if($test)
            {
                $latdata = $paymentInstance->getAdditionalInformation();
                var_dump($latdata);

            }
            if($method == 'latipay')
            {
                $tosync = false;
                $status_history = $order->getStatusHistories();
                foreach ($status_history as $status) {
                    //echo $status->getStatusLabel() . "- " . $status->getComment() . " (on " . $status->getCreatedAt() . ")\n";
                    $comment = $status->getComment();
                    if(!empty($comment))
                    {
                        $myjson = str_replace("Latipay Response :", "",$comment);
                        //echo $myjson ."\n";
                        $myarray = json_decode($myjson, true);
                        var_dump($myarray);
                        if(isset($myarray['status']))
                        {
                            $latistatus = $myarray['status'];
                            if($latistatus == 'paid')
                            {
                                $tosync = true;
                            }
                            else {
                                $tosync = false;
                            }
                        }
                        else
                        {
                            $tosync = false;
                        }

                    }

                }

                if(!$tosync)
                {
                    continue;
                }

            }

            if (empty($payment_reference) && ($method == 'latipay')) {
                //$payment_reference = $paymentInstance->getAdditionalInformation('klarna_order_id');
                //if (empty($payment_reference)){
                if(!$test)
                {
                    continue;
                }
                //}

            }

            //ebay
            if (($method == 'm2epropayment')) {
                if($paymentInstance->getAdditionalInformation('component_mode') == 'ebay')
                {
                    $payment_reference = $paymentInstance->getAdditionalInformation('channel_order_id');
                }
                else if (empty($payment_reference))
                {
                    $payment_reference = $paymentInstance->getAdditionalInformation('channel_order_id');
                }
            }

            if (($payment_type == 'ZM')) {

                $payment_reference = $paymentInstance->getAdditionalInformation('receipt_number');
//               if($payment_reference == '')
//               {
//                   $payment_reference = $paymentInstance->getAdditionalInformation('zip_checkout_id');
//               }
            }

            //paypal express fix
            if (($payment_type == 'PX')) {

                $payment_status = $paymentInstance->getAdditionalInformation('paypal_payment_status');
                if($payment_status == 'pending')
                {
                    echo "pending paypal <br />";
                    continue;
                }
            }

//            $liabilityShift = false;
//            if (($payment_type == 'BT')) {
//
//                $liabilityShifted = $paymentInstance->getAdditionalInformation('liabilityShifted');
//                if($liabilityShifted == 'Yes')
//                {
//                    $liabilityShift = true;
//                }
//            }


            $withpaymentref = true;
            if(($payment_type == "Y"))
            {
                $withpaymentref = false;
            }
            if(($payment_type == "H"))
            {
                $withpaymentref = false;
            }
            if(($payment_type == "VI"))
            {
                $withpaymentref = false;
            }
            //gift cards
//            $withGC = false;
//            $gift_amount = $order->getGiftCardsAmount();
//            echo "gift_amount ".$gift_amount."<br/>";
//
//            if($gift_amount > 0)
//            {
//                $withGC = true;
//                $gift_amount = round($gift_amount, 2);
//                $gc_data = $order->getGiftCards();
//                $arr = explode(",",$gc_data);
//                $gc_ref = explode(":", $arr[1]);
//                $gc_reference = $gc_ref[1];
//                if($test)
//                {
//                    echo "gc ref " .$gc_reference;
//                }
//
//                $sellerdata['sales-order']['header']['payment-details']['payment-detail'][0]['payment-type'] = "VI";
//                $sellerdata['sales-order']['header']['payment-details']['payment-detail'][0]['payment-reference'] = $gc_reference;
//                $sellerdata['sales-order']['header']['payment-details']['payment-detail'][0]['amount-tendered'] = $gift_amount;
//            }

            $amount_tendered = $order->getBaseGrandTotal();
            $amount_tendered = round($amount_tendered, 2);

            if($withpaymentref)
            {
//                if($withGC)
//                {
//                    $sellerdata['sales-order']['header']['payment-details']['payment-detail'][1]['payment-type'] = $payment_type;
//                    $sellerdata['sales-order']['header']['payment-details']['payment-detail'][1]['payment-reference'] = $payment_reference." ".$cc;
//                    $sellerdata['sales-order']['header']['payment-details']['payment-detail'][1]['amount-tendered'] = $amount_tendered;
//                }
//                else
//                {
                    $sellerdata['sales-order']['header']['payment-details']['payment-detail']['payment-type'] = $payment_type;
                    $sellerdata['sales-order']['header']['payment-details']['payment-detail']['payment-reference'] = $payment_reference." ".$cc;
                    $sellerdata['sales-order']['header']['payment-details']['payment-detail']['amount-tendered'] = $amount_tendered;
                //}
            }


            $sellerdata['sales-order']['header']['custom-data']['data'][0]['key'] = 'magento-order-number';
            $sellerdata['sales-order']['header']['custom-data']['data'][0]['value'] = $orderId;

            $sellerdata['sales-order']['header']['custom-data']['data'][1]['key'] = 'email';
            $sellerdata['sales-order']['header']['custom-data']['data'][1]['value'] = $customerEmail;

            if (!$order->getCustomerIsGuest()) {
                $customerRep = $this->customerRepository->getById($order->getCustomerId());
                $customerGroupId = $customerRep->getGroupId();
                if($customerGroupId == 10)
                {
                    $sellerdata['sales-order']['header']['custom-data']['data'][2]['key'] = 'marketing-flag';
                    $sellerdata['sales-order']['header']['custom-data']['data'][2]['value'] = 'CLUB';
                }
            }


            //for coupon
            $coupon = $order->getCouponCode();
            $couponDiscount = ((double) $order->getBaseDiscountAmount());

            //product lines
            // for redeploy
            $x = 0;
            $producttotal = 0;
            $gotdigi = 0;
            foreach ($order->getAllVisibleItems() as $item)
            {
                /* @var $item \Magento\Sales\Model\Order\Item */

                $skus = array();
                $productSku = "";
                $digiProtect = "";
                $price = (double) $item->getBasePriceInclTax();
                $qty = (double) $item->getQtyOrdered();
                $discount = (double) $item->getDiscountAmount();
                $total = ($price * $qty) - $discount;
                $discperc = 0;
                if($price > 0)
                {
                    $discperc = ($discount / $price) * 100;
                }
                if($coupon != "")
                {
                    $discount = 0;
                    $discperc = 0;
                }
                $digiProtectPrice = 0;
                $digiProtectQty = 0;
                $digiProtectdiscount = 0;
                $digiProtectTotal = 0;


                $sku = $item->getSku();
                $productDetails = $this->productFactory->create();


                if(strpos($sku, 'mp-') !== false)
                {
                    //check seller here
                    $sell = $productDetails->loadByAttribute('sku', $sku)->getMarketplacerSeller();

                    if($sell == $seller)
                    {
                        $productSku = $sku;
                        $gst = $price - ($price*100 / (100+10));
                        $gst = number_format($gst,2);
                        $costlessgst = $price - $gst;
                        $commission = $costlessgst * 0.1;
                        $exgstcost = $costlessgst - $commission;
                        $sellerdata['sales-order']['header']['set-on-status'] = "B";
                        $sellerdata['sales-order']['detail']['line'][$x]['line-type'] = 'SS';
                        $sellerdata['sales-order']['detail']['line'][$x]['stock-code'] = 'ZM00';//$productSku;
                        $sellerdata['sales-order']['detail']['line'][$x]['description'] = $item->getName();
                        $sellerdata['sales-order']['detail']['line'][$x]['unit-price-inc-tax'] = $price;
                        $sellerdata['sales-order']['detail']['line'][$x]['ordered'] = $qty;
                        $sellerdata['sales-order']['detail']['line'][$x]['shipped'] = $qty;
                        $sellerdata['sales-order']['detail']['line'][$x]['backordered'] = 0;
                        $sellerdata['sales-order']['detail']['line'][$x]['sol-disc-rate'] = $discperc;
                        $sellerdata['sales-order']['detail']['line'][$x]['sol-line-total-inc-tax'] = $total;
                        $sellerdata['sales-order']['detail']['line'][$x]['item-cost'] = $exgstcost;
                        $x++;
                        $producttotal += $total;
                    }
                    else
                    {
                        $gotdigi = 1;
                    }

                }
                else //
                {
                    $gotdigi = 1;

                }

            } //end of product line


            if($gotdigi == 0)
            {
                if($surcharge != "0.0000")
                {
                    $sellerdata['sales-order']['detail']['line'][$x]['line-type'] = 'SC';
                    $sellerdata['sales-order']['detail']['line'][$x]['description'] = "Surcharge";
                    $sellerdata['sales-order']['detail']['line'][$x]['unit-price-inc-tax'] = $surcharge;
                    $sellerdata['sales-order']['detail']['line'][$x]['ordered'] = 1;
                    $sellerdata['sales-order']['detail']['line'][$x]['shipped'] = 1;
                    $sellerdata['sales-order']['detail']['line'][$x]['sol-disc-rate'] = 0;
                    $sellerdata['sales-order']['detail']['line'][$x]['sol-chg-type'] = "C3";
                    $sellerdata['sales-order']['detail']['line'][$x]['sol-line-total-inc-tax'] = $surcharge;
                    $producttotal += $surcharge;
                    $x++; // for shipping counter
                }

//                if($coupon != "")
//                {
//                    $sellerdata['sales-order']['detail']['line'][$x]['line-type'] = 'SC';
//                    $sellerdata['sales-order']['detail']['line'][$x]['description'] = $coupon;
//                    $sellerdata['sales-order']['detail']['line'][$x]['unit-price-inc-tax'] = $couponDiscount;
//                    $sellerdata['sales-order']['detail']['line'][$x]['ordered'] = 1;
//                    $sellerdata['sales-order']['detail']['line'][$x]['shipped'] = 1;
//                    $sellerdata['sales-order']['detail']['line'][$x]['sol-disc-rate'] = 0;
//                    $sellerdata['sales-order']['detail']['line'][$x]['sol-chg-type'] = "C5";
//                    $sellerdata['sales-order']['detail']['line'][$x]['sol-line-total-inc-tax'] = $couponDiscount;
//                    $x++; // for shipping counter
//                }


                $shippingprice = (double) $order->getShippingAmount();
                $shippingDesc = $order->getShippingDescription();

                if (strpos($orderId, 'REEB') !== false) {
                    $shippingDesc = "Australia Post – eParcel";
                }
                else
                {
                    if (strpos($shippingDesc, '|') !== false) {
                        $marketplacesShipping = explode('|', $shippingDesc);
                        $shippingDesc = $marketplacesShipping[1];
                    }
                }
                if($shippingDesc == "Express - (1 to 3 Days)")
                {
                    $shippingDesc = "Australia Post – express";
                }
                else if($shippingDesc == "Standard - (4 to 7 Days)")
                {
                    $shippingDesc = "Australia Post – eParcel";
                }
                else if($rep == 'WESTFIELD')
                {
                    $shippingDesc = "Click and Collect";
                }
                else if($shippingDesc == "AU_ExpressPostParcelSignature")
                {
                    $shippingDesc = "Australia Post – express";
                }
                else if($shippingDesc == "AU_RegularParcelWithTrackingAndSignature")
                {
                    $shippingDesc = "Australia Post – eParcel";
                }
                else
                {
                    $shippingDesc = "";
                }
                //shipping details clint Mar 3 23
                if($disregardshipping)
                {
                    $shippingprice = 0;
                }
                $sellerdata['sales-order']['detail']['line'][$x]['line-type'] = 'SC';
                $sellerdata['sales-order']['detail']['line'][$x]['description'] = $shippingDesc;
                $sellerdata['sales-order']['detail']['line'][$x]['unit-price-inc-tax'] = $shippingprice;
                $sellerdata['sales-order']['detail']['line'][$x]['ordered'] = 1;
                $sellerdata['sales-order']['detail']['line'][$x]['shipped'] = 1;
                $sellerdata['sales-order']['detail']['line'][$x]['sol-disc-rate'] = 0;
                $sellerdata['sales-order']['detail']['line'][$x]['sol-chg-type'] = "C1";
                $sellerdata['sales-order']['detail']['line'][$x]['sol-line-total-inc-tax'] = $shippingprice;
                $producttotal += $shippingprice;
            }

            //fixed shipping price as interim
//            if($producttotal > 99)
//            {
//                $shippingprice = 0;
//                $shippingDesc = "Standard";
//            }
//            else
//            {
//                $shippingprice = 10;
//                $shippingDesc = "Free Shipping";
//            }

            //sync only product total
            if($withpaymentref)
            {
                $sellerdata['sales-order']['header']['payment-details']['payment-detail']['amount-tendered'] = $producttotal;
            }

//            $sellerdata['sales-order']['detail']['line'][$x]['line-type'] = 'SC';
//            $sellerdata['sales-order']['detail']['line'][$x]['description'] = $shippingDesc;
//            $sellerdata['sales-order']['detail']['line'][$x]['unit-price-inc-tax'] = $shippingprice;
//            $sellerdata['sales-order']['detail']['line'][$x]['ordered'] = 1;
//            $sellerdata['sales-order']['detail']['line'][$x]['shipped'] = 1;
//            $sellerdata['sales-order']['detail']['line'][$x]['sol-disc-rate'] = 0;
//            $sellerdata['sales-order']['detail']['line'][$x]['sol-chg-type'] = "C1";
//            $sellerdata['sales-order']['detail']['line'][$x]['sol-line-total-inc-tax'] = $shippingprice;

            //if($test)
            //{
            var_dump($sellerdata['sales-order']);
            //}
            //create xml of order data here
            //$this->logger->info('Pronto Order Sync Data - ',$data['sales-order']);
            $xml = \Digidirect\AI\Model\Lib\Adapter\Import\Xml::assocToXml($sellerdata, 'sales-orders');

            //TEST
            //$url = 'https://digi-pronto.abtonline.com.au:8083/rest/abtws/sales?call-type=create_orders'; //TEST

            //LIVE - port :8084
            $url = 'https://digi-pronto.abtonline.com.au:8084/rest/abtws/sales?call-type=create_orders';


            if(!$test)
            {
                $this->curl->addHeader("Content-Type", "application/xml");
                $this->curl->addHeader("Accept", "application/json");
                $this->curl->addHeader("compcode", "DIG"); //live
                $this->curl->addHeader("user", "ewaveapi");
                $this->curl->addHeader("token", "904241bdbf10efa9");
                //
//                $this->curl->addHeader("compcode", "UA1"); //test
//                $this->curl->addHeader("user", "clint.mercado");
//                $this->curl->addHeader("token", "849cd5080faff5ce");

                $this->curl->setOption(CURLOPT_SSL_VERIFYHOST,false);
                $this->curl->setOption(CURLOPT_SSL_VERIFYPEER,false);
                $this->curl->post($url, $xml);

                $result = $this->curl->getBody();

                $json = $this->jsonSerializer->unserialize($result);

                if(isset($json['response']['status']) && ($json['response']['status'] == 'FAIL'))
                {
                    $msg =  $json['response']['message'];
                    echo "<br> fail - ".$msg."<br>";
                    $this->logger->error('Pronto Order Sync', array('info' => $msg));

                }
                else if (isset($json['sales-orders']['response']['status']) && ($json['sales-orders']['response']['status'] == 'failed')) {
                    $msg =  $json['sales-orders']['response']['message'];
                    echo "<br> fail - ".$msg."<br>";
                    $this->logger->error('Pronto Order Sync', array('info' => $msg));

                }
                else {
                    $this->syncedseller = $seller;
                    $pronto = $json['sales-orders']['sales-order']['order-no'];
                    $invoiceno = $json['sales-orders']['sales-order']['invoice-no'];
                    $prontostatus = $json['sales-orders']['sales-order']['order-status-code'];
                    $newaccount = $json['sales-orders']['sales-order']['account'];

                    $order->setData('pronto_order_number',$pronto);
                    $order->setData('pronto_status_code',$prontostatus);
                    $order->save();

                    $this->logger->info('Pronto Order Sync ', $json['sales-orders']['sales-order']);
                    var_dump($json['sales-orders']['sales-order']);
                    return $newaccount;
                }
            }

            return;

        }

    }

    public function getTestOrderCollection($orderId, $date, $size, $page)
    {
        if($orderId != '0')
        {
            $collection = $this->_orderCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('increment_id', array('eq' => $orderId));

            return $collection;
        }
        if($date != 0)
        {
            settype($size,"integer");
            settype($page,"integer");
            $fromDate = date('Y-m-d'. ' 00:00:00',strtotime($date));
            $toDate = date('Y-m-d'. ' 23:59:59',strtotime($date));
            $collection = $this->_orderCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('pronto_order_number', array('null' => true))
                ->addFieldToFilter('created_at', array('gteq' => $fromDate))
                ->addFieldToFilter('created_at', array('lteq' => $toDate));
            //->setCurPage($page);
            return $collection;
        }

    }

    public function getCCType($paymentInstance)
    {
        if($type == 'BT')
        {
            $cc = $paymentInstance->getCcType();
            if($cc == 'AE')
            {
                $type = 'X';
            }
        }

        return;
    }

    public function populateMagento($orderId, $pronto)
    {

        $orders = $this->getOrderToPopulate($orderId);
        $counter = 0;
        foreach ($orders as $order) {

            echo 'Magento Order Sync - '.$orderId.' with '.$pronto.'<br>';

            $order->setData('pronto_order_number',$pronto);
            $order->save();
            //comment to redeploy
            exit; //for testing;
        }

    }

    public function getOrderToPopulate($orderId)
    {

        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('increment_id', array('eq' => $orderId));

        return $collection;

    }
    //redeploy
}
