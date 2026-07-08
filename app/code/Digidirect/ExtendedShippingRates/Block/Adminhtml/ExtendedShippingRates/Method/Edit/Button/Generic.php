<?php
namespace Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates\Method\Edit\Button;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Digidirect\ExtendedShippingRates\Model\Carrier\Method;
use Magento\Framework\App\RequestInterface;
use Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Method as MethodController;
use Digidirect\ExtendedShippingRates\Model\Carrier\MethodFactory;

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
     * @var MethodFactory
     */
    protected $methodFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param RequestInterface $request
     * @param MethodFactory $methodFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        RequestInterface $request,
        MethodFactory $methodFactory
    ) {
        $this->context = $context;
        $this->registry = $registry;
        $this->request = $request;
        $this->methodFactory = $methodFactory;
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
     * Get method: current or empty
     *
     * @return \Digidirect\ExtendedShippingRates\Model\Carrier\Method
     */
    public function getMethod()
    {
        $method = $this->registry->registry(Method::CURRENT_METHOD);
        if (!$method) {
            $method = $this->methodFactory->create();
        }

        return $method;
    }

    /**
     * Check is need to return admin to the carrier edit controller
     *
     * @return bool
     */
    public function isBackToCarrier()
    {
        if ($this->request->getParam(MethodController::BACK_TO_PARAM) == MethodController::BACK_TO_CARRIER_PARAM) {
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
