<?php

namespace CommissionFactory\Tracking\Observer;

class Container implements \Magento\Framework\Event\ObserverInterface
{
	private $_cookieManager;
	private $_cookieMetadataFactory;
	private $_request;
	private $_sessionManager;

	public function __construct(\Magento\Framework\Stdlib\CookieManagerInterface $cookieManager, \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory, \Magento\Framework\App\Request\Http $request, \Magento\Framework\Session\SessionManagerInterface $sessionManager)
	{
		$this->_cookieManager = $cookieManager;
		$this->_cookieMetadataFactory = $cookieMetadataFactory;
		$this->_request = $request;
		$this->_sessionManager = $sessionManager;
	}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$cfclick = $this->_request->getParam("cfclick");

		if (!empty($cfclick))
		{
			$metadata = $this->_cookieMetadataFactory->createPublicCookieMetadata()
				->setDomain($this->_sessionManager->getCookieDomain())
				->setDuration(31104000)
				->setHttpOnly(true)
				->setPath($this->_sessionManager->getCookiePath())
				->setSecure(true);

			$this->_cookieManager->setPublicCookie("cfjump-server-click", $cfclick, $metadata);
		}
	}
}
