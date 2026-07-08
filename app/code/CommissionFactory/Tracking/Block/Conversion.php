<?php

namespace CommissionFactory\Tracking\Block;

class Conversion extends \Magento\Framework\View\Element\Template
{
	private $_commissionFactoryTrackingData;
	private $_cookieManager;
	private $_curl;
	private $_salesOrderCollection;

	public function __construct(\Magento\Framework\View\Element\Template\Context $context, \CommissionFactory\Tracking\Helper\Data $commissionFactoryTrackingData, \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager, \Magento\Framework\HTTP\Client\Curl $curl, \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection, array $data = [ ])
	{
		$this->_commissionFactoryTrackingData = $commissionFactoryTrackingData;
		$this->_cookieManager = $cookieManager;
		$this->_curl = $curl;
		$this->_salesOrderCollection = $salesOrderCollection;

		parent::__construct($context, $data);
	}

	protected function _toHtml()
	{
		$advertiserId = $this->_commissionFactoryTrackingData->getAdvertiserId();

		if (!$advertiserId)
		{
			return "";
		}

		$orderIds = $this->getOrderIds();

		if (empty($orderIds) || !is_array($orderIds))
		{
			return "";
		}

		$this->_curl->addHeader("Content-Type", "application/json; charset=utf-8");

		$html = "";

		$collection = $this->_salesOrderCollection->create();

		$collection->addFieldToFilter("entity_id", [ "in" => $orderIds ]);

		foreach ($collection as $order)
		{
			$orderId = $order->getIncrementId();
			$amount = $order->getSubtotal() + $order->getDiscountAmount() + $order->getHiddenTaxAmount();
			$currency = $order->getOrderCurrencyCode();
			$coupon = $order->getCouponCode();
			$customer = $order->getCustomerIsGuest() ? null : (count($this->_salesOrderCollection->create()->addFieldToFilter("customer_id", $order->getCustomerId())) == 1 ? "new" : "return");
			$items = $order->getAllVisibleItems();

			$s2s = array();

			$s2s["merchant"] = $advertiserId;
			$s2s["click"] = $this->_cookieManager->getCookie("cfjump-server-click");
			$s2s["order"] = $orderId;
			$s2s["amount"] = $amount;
			$s2s["currency"] = $currency;
			$s2s["coupon"] = $coupon;

			if (isset($customer))
			{
				$s2s["customer"] = $customer;
			}

			$s2s["items"] = array();

			foreach ($items as $item)
			{
				$s2s["items"][] = array("sku" => $item->getSku(), "price" => $item->getPrice(), "quantity" => $item->getQtyOrdered());
			}

			$this->_curl->post("https://t.cfjump.com/s2s", json_encode($s2s));

			$html .= "<script>\n";
			$html .= "    (function(a,b,c){a[b]=a[b]||function(){(a[b].q=a[b].q||[]).push(arguments);};a[c]=a[b];})(window,\"CommissionFactory\",\"cf\");\n";
			$html .= "\n";
			$html .= "    cf(\"set\", \"order\", " . json_encode($orderId) . ");\n";
			$html .= "    cf(\"set\", \"amount\", " . json_encode($amount) . ");\n";
			$html .= "    cf(\"set\", \"currency\", " . json_encode($currency) . ");\n";
			$html .= "    cf(\"set\", \"coupon\", " . json_encode($coupon) . ");\n";

			if (isset($customer))
			{
				$html .= "    cf(\"set\", \"customer\", " . json_encode($customer) . ");\n";
			}

			$html .= "\n";

			foreach ($items as $item)
			{
				$html .= "    cf(\"add\", \"items\", { \"sku\": " . json_encode($item->getSku()) . ", \"price\": " . json_encode($item->getPrice()) . ", \"quantity\": " . json_encode($item->getQtyOrdered()) . " });\n";
			}

			$html .= "\n";
			$html .= "    cf(\"track\");\n";
			$html .= "</script>\n";
		}

		return $html;
	}
}
