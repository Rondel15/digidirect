<?php

namespace Digidirect\CheckoutFields\Observer;

use Digidirect\CheckoutFields\Api\Data\PdpFieldValueInterface;
use Digidirect\CheckoutFields\Helper\Config;
use Digidirect\CheckoutFields\Model\PdpFieldValueFactory;
use Digidirect\CheckoutFields\Model\PdpFieldValueRepository;
use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer;
use \Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class SaveCustomProductFieldValuesObserver
 * @package Digidirect\CheckoutFields\Observer
 */
class SaveCustomProductFieldValuesObserver implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var Config
     */
    protected $_config;

    /**
     * @var PdpFieldValueFactory
     */
    protected $_factory;

    /**
     * @var PdpFieldValueRepository
     */
    protected $_repository;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * SaveCustomCheckoutValuesToQuoteObserver constructor.
     * @param RequestInterface $request
     * @param Config $config
     * @param PdpFieldValueFactory $factory
     * @param PdpFieldValueRepository $repository
     * @param CheckoutSession $session
     */
    public function __construct(
        RequestInterface $request,
        Config $config,
        PdpFieldValueFactory $factory,
        PdpFieldValueRepository $repository,
        CheckoutSession $session
    ) {
        $this->_request = $request;
        $this->_config = $config;
        $this->_factory = $factory;
        $this->_checkoutSession = $session;
        $this->_repository = $repository;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function execute(Observer $observer)
    {
        $params = $this->_request->getParams();
        $pdpFields = $this->_config->getActivePDPFields(null);
        $checkedPdpFields = array_intersect_key($params, $pdpFields);

        $quoteId = $this->_checkoutSession->getQuote()->getId();
        $productId = $observer->getEvent()->getProduct()->getId();

        foreach ($pdpFields as $key => $value) {
            $data = [
                PdpFieldValueInterface::QUOTE_ID => $quoteId,
                PdpFieldValueInterface::PRODUCT_ID => $productId,
                PdpFieldValueInterface::FIELD_CODE => $key,
                PdpFieldValueInterface::FIELD_VALUE => array_key_exists($key, $checkedPdpFields) ? 1 : 0,
            ];
            $model = $this->_factory->create();
            $model->setData($data);
            $this->_repository->save($model);   // @todo save several models at one time?
        }

        return $this;
    }
}
