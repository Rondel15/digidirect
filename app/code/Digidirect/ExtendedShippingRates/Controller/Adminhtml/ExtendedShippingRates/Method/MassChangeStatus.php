<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Method;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Digidirect\ExtendedShippingRates\Model\ResourceModel\Method\CollectionFactory;
use Digidirect\ExtendedShippingRates\Model\Carrier\MethodFactory;
use Digidirect\ExtendedShippingRates\Api\MethodRepositoryInterface;

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
    protected $methodCollectionFactory;

    /**
     * @var MethodFactory
     */
    protected $methodFactory;

    /**
     * @var MethodRepositoryInterface
     */
    protected $methodRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $methodCollectionFactory
     * @param MethodFactory $methodFactory
     * @param MethodRepositoryInterface $methodRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $methodCollectionFactory,
        MethodFactory $methodFactory,
        MethodRepositoryInterface $methodRepository
    ) {
        parent::__construct($context);
        $this->methodCollectionFactory = $methodCollectionFactory;
        $this->filter = $filter;
        $this->methodFactory = $methodFactory;
        $this->methodRepository = $methodRepository;
    }

    /**
     * Update methods's is active status
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->methodCollectionFactory->create());
            $updatedMethodCount = 0;
            foreach ($collection->getAllIds() as $methodId) {
                $method = $this->methodRepository->getById($methodId);
                $method->setData('active', $this->getRequest()->getParam('active'));
                $this->methodRepository->save($method);
                $updatedMethodCount++;
            }

            if ($updatedMethodCount) {
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) were updated.', $updatedMethodCount)
                );
            }

            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory
                ->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('digidirect_extendedshippingrates/extendedshippingrates_method/index');

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
        return $this->_authorization->isAllowed('Digidirect_ExtendedShippingRates::carrier');
    }
}
