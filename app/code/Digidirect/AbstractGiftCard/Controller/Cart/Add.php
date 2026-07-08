<?php

namespace Digidirect\AbstractGiftCard\Controller\Cart;

class Add extends \Digidirect\AbstractGiftCard\Controller\AbstractController
{
    /**
     * Add Gift Card to current quote
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            $serviceInstance = $this->_initService();
            if ($serviceInstance->canCheckStatus()) {
                $serviceInstance->validate()->checkStatus();
            }
            if (!$serviceInstance->getGiftCardAccount()) {
                $this->messageManager->addError(
                    'Something went wrong with processing a Gift Card. Please try again later'
                );
                return $this->_goBack();
            }
            $serviceInstance->getGiftCardAccount()->addToCart();
            $this->messageManager->addSuccess(__('Gift Card was added.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We cannot apply this gift card.'));
        }
        return $this->_goBack();
    }
}
