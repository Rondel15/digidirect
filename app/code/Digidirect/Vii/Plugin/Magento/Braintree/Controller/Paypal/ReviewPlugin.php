<?php

namespace Digidirect\Vii\Plugin\Magento\Braintree\Controller\Paypal;

use Magento\Braintree\Controller\Paypal\Review;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Digidirect\Vii\Service\Config\Config;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Digidirect\AbstractGiftCard\Helper\Data;

/**
 * Class ReviewPlugin
 * @package Digidirect\Vii\Plugin\Magento\Braintree\Controller\Paypal
 */
class ReviewPlugin
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * ReviewPlugin constructor.
     * @param Context $context
     * @param Config $config
     * @param Session $checkoutSession
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Config $config,
        Session $checkoutSession,
        Data $helper
    ) {
        $this->messageManager = $context->getMessageManager();
        $this->resultFactory = $context->getResultFactory();
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
    }

    /**
     * @param Review $subject
     * @param \Magento\Framework\Controller\Result\Redirect|ResultInterface $result
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(Review $subject, $result)
    {
        if (!$this->helper->isActive() || !$this->config->isActive()) {
            return $result;
        }

        if (!$result instanceof ResultInterface) {
            return $result;
        }

        if (!(float)$this->checkoutSession->getQuote()->getGrandTotal()) {
            $exception = new LocalizedException(
                __(
                    'PayPal can\'t process orders with a zero balance due. '
                    . 'To finish your purchase, please go through the standard checkout process.'
                )
            );
            $this->messageManager->addExceptionMessage($exception);
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
        }

        return $result;
    }
}
