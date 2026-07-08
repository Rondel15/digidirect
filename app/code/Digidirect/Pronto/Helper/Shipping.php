<?php

namespace Digidirect\Pronto\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Psr\Log\LoggerInterface;

class Shipping extends AbstractHelper
{

    /**
    * @var Curl
    */
    protected $curl;

    protected $_orderCollectionFactory;
    /**
     * @var OrderResource
     */
    protected $orderResource;

    public function __construct(
                        Curl $curl,
                        OrderResource $orderResource,
                        JsonSerializer $jsonSerializer,
                        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
                        LoggerInterface $logger)
                    {
                        $this->curl = $curl;
                        $this->orderResource = $orderResource;
                        $this->jsonSerializer = $jsonSerializer;
                        $this->_orderCollectionFactory = $orderCollectionFactory;
                        $this->logger = $logger;

    }

    public function getShipping($pronto)
    {
        //get order data
        $orders = $this->getOrderCollection($pronto);
        //$counter = 0;
        foreach ($orders as $order) {
        //    $data = array();
        //    $counter++;
            //var_dump($order);
            /* @var $order \Magento\Sales\Model\Order */

        //    if ($order->getState() == 'canceled') {
        //        continue;
        //    }

        //    $prontoOrderNumber = $order->getData('pronto_order_number');

            $this->logger->info('Pronto Order Shipping - '.$pronto);

            //TEST
            $url = 'https://digi-pronto.abtonline.com.au:8083/rest/abtws/sales?call-type=get_order&order-no='.$pronto; //test
            //LIVE - port :8084
            //$url = 'https://digi-pronto.abtonline.com.au:8084/rest/abtws/sales?call-type=get_order&order-no='.$prontoOrderNumber; //LIVE
            $username = 'clint.mercado';
            $password = '849cd5080faff5ce';
            $jsonData = '{}';

            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("Accept", "application/json");
            $this->curl->addHeader("compcode", "DIG"); //LIVE
            $this->curl->addHeader("user", "ewaveapi");
            $this->curl->addHeader("token", "904241bdbf10efa9");
            //
            //$this->curl->addHeader("compcode", "UA1"); //test
            //$this->curl->addHeader("user", "clint.mercado");
            //$this->curl->addHeader("token", "849cd5080faff5ce");
            $this->curl->setOption(CURLOPT_SSL_VERIFYHOST,false);
            $this->curl->setOption(CURLOPT_SSL_VERIFYPEER,false);
            $this->curl->get($url);

            $result = $this->curl->getBody();

            if(!empty($result))
            {

                $json = $this->jsonSerializer->unserialize($result);
                //var_dump($json);

                if(isset($json['response']['status']) && ($json['response']['status'] == 'FAIL'))
                {
                    $msg =  $json['response']['message'];
                    $this->logger->error('Pronto Order Shipping', array('info' => $msg));

                }
                else if (isset($json['sales-orders']['response']['status']) && ($json['sales-orders']['response']['status'] == 'failed')) {
                    $msg =  $json['sales-orders']['response']['message'];
                    $this->logger->error('Pronto Order Shipping', array('info' => $msg));

                }
                else {
                    echo "success";
                    //update order data with pronto data
                    //$json['sales-order']['sales-order']['header'];
                    var_dump($json['sales-order']['header']);
                    if (!empty($json['sales-order']['header']['so-consignment-note'])) {
                        $order->setData('pronto_order_tracking_number', $json['sales-order']['header']['so-consignment-note']);
                        $this->orderResource->saveAttribute($order, 'pronto_order_tracking_number');
                        $order->setState("complete")->setStatus("complete");
                    } else {
                        $this->logger->info('Cannot update Order Tracking Number');
                    }

                    if (!empty($json['sales-order']['header']['so-user-only-alpha20-1'])) {
                        $order->setData('pronto_manifest_number', $json['sales-order']['header']['so-user-only-alpha20-1']);
                        $this->orderResource->saveAttribute($order, 'pronto_manifest_number');
                    } else {
                        $this->logger->info('Cannot update Order Manifest Number');
                    }

                }
            }
            else
            {
                echo "no result";
            }
        }

    }

    public function getOrderCollection($pronto)
    {

        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('pronto_order_number', array('eq' => $pronto));
     return $collection;

    }

    public function getBulkOrderCollection()
    {
        $now = new \DateTime();
        $fromDate = date('Y-m-d 00:00:00', strtotime('2021-07-01'));
        $toDate = $now->format('Y-m-d h:i:s');
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('pronto_order_number', array('neq' => null))
            ->addFieldToFilter('pronto_order_tracking_number',array('null' => true))
            ->addFieldToFilter('created_at', array('gteq' => $fromDate))
            ->addFieldToFilter('created_at', array('lteq' => $toDate))
            ->setOrder('created_at', 'asc')
            ->setPageSize(25);

     return $collection;

    }

}
