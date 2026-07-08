<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Zone;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Digidirect\ExtendedShippingRates\Model\ResourceModel\Zone\CollectionFactory;
use Digidirect\ExtendedShippingRates\Api\ZoneRepositoryInterface;

class MassDelete extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $zoneCollectionFactory;

    /**
     * @var ZoneRepositoryInterface
     */
    protected $zoneRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $zoneCollectionFactory
     * @param ZoneRepositoryInterface $zoneRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $zoneCollectionFactory,
        ZoneRepositoryInterface $zoneRepository
    ) {
        $this->filter = $filter;
        $this->zoneCollectionFactory = $zoneCollectionFactory;
        $this->zoneRepository = $zoneRepository;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $collection = $this->filter->getCollection(
            $this->zoneCollectionFactory->create()
        );
        $ids = $collection->getAllIds();
        $size = count($ids);
        foreach ($ids as $id) {
            $this->zoneRepository->deleteById($id);
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
        return $this->_authorization->isAllowed('Digidirect_ExtendedShippingRates::zone');
    }
}
