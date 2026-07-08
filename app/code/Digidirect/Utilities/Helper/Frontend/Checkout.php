<?php

namespace Digidirect\Utilities\Helper\Frontend;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Payment\Model\InfoInterface;

/**
 * @since 1.15.2
 */
class Checkout extends AbstractHelper
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Emulation
     */
    protected $emulation;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * Checkout constructor.
     *
     * @param Context $context
     * @param Session $session
     * @param Emulation $emulation
     * @param LayoutInterface $layout
     */
    public function __construct(Context $context, Session $session, Emulation $emulation, LayoutInterface $layout)
    {
        parent::__construct($context);
        $this->checkoutSession = $session;
        $this->emulation = $emulation;
        $this->layout = $layout;
    }

    /**
     * @return Session
     */
    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getLastOrder()
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    /**
     * @param InfoInterface $info
     * @param int $storeId
     * @param bool $emulate
     * @return mixed
     * @throws \Exception
     */
    public function getInfoBlockHtml(InfoInterface $info, $storeId, $emulate = false)
    {
        $this->startRender($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, $emulate);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = $this->getInfoBlock($info);
            $paymentBlock->setArea(\Magento\Framework\App\Area::AREA_FRONTEND)
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()
                ->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (\Exception $exception) {
            $this->stopRender($emulate);
            throw $exception;
        }

        $this->stopRender($emulate);
        return $paymentBlockHtml;
    }

    /**
     * @param int $storeId
     * @param string $area
     * @param bool $emulate
     * @return $this
     */
    protected function startRender($storeId, $area = \Magento\Framework\App\Area::AREA_FRONTEND, $emulate = false)
    {
        if ($emulate) {
            $this->emulation->startEnvironmentEmulation($storeId, $area, $emulate);
        }
        return $this;
    }

    /**
     * @param bool $emulate
     * @return $this
     */
    protected function stopRender($emulate = false)
    {
        if ($emulate) {
            $this->emulation->stopEnvironmentEmulation();
        }
        return $this;
    }

    /**
     * Retrieve payment information block
     *
     * @param InfoInterface $info
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @return Template
     */
    public function getInfoBlock(InfoInterface $info, LayoutInterface $layout = null)
    {
        $layout = $layout ?: $this->layout;
        $blockType = $info->getMethodInstance()->getInfoBlockType();
        $block = $layout->createBlock($blockType);
        $block->setInfo($info);
        return $block;
    }
}
