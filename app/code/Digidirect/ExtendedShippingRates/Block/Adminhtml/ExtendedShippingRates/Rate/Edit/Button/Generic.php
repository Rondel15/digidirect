<?php

namespace Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates\Rate\Edit\Button;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate;
use Magento\Framework\App\RequestInterface;
use Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Rate as RateController;
use Digidirect\ExtendedShippingRates\Model\Carrier\Method\RateFactory;

class Generic implements ButtonProviderInterface
{
    /**
     * Url Builder
     *
     * @var Context
     */
    protected $context;

    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var RateFactory
     */
    protected $rateFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param RequestInterface $request
     * @param RateFactory $rateFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        RequestInterface $request,
        RateFactory $rateFactory
    ) {
        $this->context = $context;
        $this->registry = $registry;
        $this->request = $request;
        $this->rateFactory = $rateFactory;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        /** @var string $url */
        $url = $this->context->getUrl($route, $params);

        return $url;
    }

    /**
     * Get rate: current or empty
     *
     * @return \Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate
     */
    public function getRate()
    {
        $rate = $this->registry->registry(Rate::CURRENT_RATE);
        if (!$rate) {
            $rate = $this->rateFactory->create();
        }

        return $rate;
    }

    /**
     * Check is need to return admin to the method edit controller
     *
     * @return bool
     */
    public function isBackToMethod()
    {
        if ($this->request->getParam(RateController::BACK_TO_PARAM) == RateController::BACK_TO_METHOD_PARAM) {
            return true;
        }

        return false;
    }

    /**
     * Get button additional data
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        return $data;
    }
}
