<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Quote;

use Digidirect\ExtendedShippingRates\Model\ResourceModel\Rule\CollectionFactory;
use Digidirect\ExtendedShippingRates\Model\RuleFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassChangeStatus extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var string
     */
    protected $redirectUrl = '*/*/index';

    /**
     * @var CollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $ruleCollectionFactory
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $ruleCollectionFactory,
        RuleFactory $ruleFactory
    ) {
        parent::__construct($context);
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->filter = $filter;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * Update rule's is active status
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->ruleCollectionFactory->create());
            $updatedRulesCount = 0;
            foreach ($collection as $rule) {
                $rule->setIsActive($this->getRequest()->getParam('is_active'));
                $rule->save();

                $updatedRulesCount++;
            }

            if ($updatedRulesCount) {
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) were updated.', $updatedRulesCount)
                );
            }

            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory
                ->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('digidirect_extendedshippingrates/extendedshippingrates_quote/index');

            return $resultRedirect;
        } catch (\Exception $e) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultFactory
                ->create(ResultFactory::TYPE_REDIRECT);

            return $resultRedirect->setPath($this->redirectUrl);
        }
    }

    /**
     * Returns result of current user permission check on resource and privilege
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Digidirect_ExtendedShippingRates::quote');
    }
}
