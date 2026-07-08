<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Quote;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Digidirect\ExtendedShippingRates\Model\ResourceModel\Rule\CollectionFactory;
use Digidirect\ExtendedShippingRates\Api\RuleRepositoryInterface;

class MassDelete extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $ruleCollectionFactory
     * @param RuleRepositoryInterface $ruleRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $ruleCollectionFactory,
        RuleRepositoryInterface $ruleRepository
    ) {
        $this->filter = $filter;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->ruleRepository = $ruleRepository;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $collection = $this->filter->getCollection(
            $this->ruleCollectionFactory->create()
        );
        $ids = $collection->getAllIds();
        $size = count($ids);
        foreach ($ids as $id) {
            $this->ruleRepository->deleteById($id);
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $this->messageManager
            ->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $size)
            );
        $resultRedirect = $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
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
