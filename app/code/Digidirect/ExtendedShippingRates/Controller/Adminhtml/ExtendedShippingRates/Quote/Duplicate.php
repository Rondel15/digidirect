<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Quote;

class Duplicate extends \Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Quote
{
    /**
     * Create rule duplicate
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->_initRule();
        /** @var \Digidirect\ExtendedShippingRates\Model\Rule $rule */
        $rule = $this->coreRegistry->registry(\Digidirect\ExtendedShippingRates\Model\Rule::CURRENT_PROMO_QUOTE_RULE);
        try {
            $newRule = clone $rule;
            $newRule->setId(null);
            $newRule->isObjectNew(true);
            $newRule->setData('is_active', 0);
            $this->ruleRepository->save($newRule);
            $this->messageManager->addSuccessMessage(__('You duplicated the rule.'));
            $resultRedirect->setPath(
                'digidirect_extendedshippingrates/*/edit',
                ['_current' => true, 'id' => $newRule->getId()]
            );
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath('digidirect_extendedshippingrates/*/edit', ['_current' => true]);
        }

        return $resultRedirect;
    }
}
