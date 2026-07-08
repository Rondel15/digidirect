<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Carrier;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Digidirect\ExtendedShippingRates\Model\ResourceModel\Carrier\CollectionFactory;
use Digidirect\ExtendedShippingRates\Api\CarrierRepositoryInterface;

class MassDelete extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $carrierCollectionFactory;

    /**
     * @var CarrierRepository
     */
    protected $carrierRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $carrierCollectionFactory
     * @param CarrierRepositoryInterface $carrierRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $carrierCollectionFactory,
        CarrierRepositoryInterface $carrierRepository
    ) {
        $this->filter = $filter;
        $this->carrierCollectionFactory = $carrierCollectionFactory;
        $this->carrierRepository = $carrierRepository;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $collection = $this->filter->getCollection(
            $this->carrierCollectionFactory->create()
        );
        $ids = $collection->getAllIds();
        $size = count($ids);
        foreach ($ids as $id) {
            $this->carrierRepository->deleteById($id);
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
        return $this->_authorization->isAllowed('Digidirect_ExtendedShippingRates::carrier');
    }
}
