<?php

namespace Digidirect\AbstractGiftCard\Controller\Cart;

use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\Controller\ResultFactory;

class QuickCheck extends \Digidirect\AbstractGiftCard\Controller\AbstractController
{
    /**
     * Check a gift card account availability
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $serviceInstance = $this->_initService();
            if (!$serviceInstance->canCheckStatus()) {
                throw new LocalizedException(__('This operation is not permitted'));
            }
            $serviceInstance->validate()->checkStatus();
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
    }
}
