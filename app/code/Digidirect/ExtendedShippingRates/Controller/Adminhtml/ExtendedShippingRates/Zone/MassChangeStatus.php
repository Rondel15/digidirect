<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Zone;

use Digidirect\ExtendedShippingRates\Api\ZoneRepositoryInterface;
use Digidirect\ExtendedShippingRates\Model\ResourceModel\Zone\CollectionFactory;
use Digidirect\ExtendedShippingRates\Model\ZoneFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassChangeStatus extends \Magento\Backend\App\Action
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
    protected $zoneCollectionFactory;

    /**
     * @var ZoneFactory
     */
    protected $zoneFactory;

    /**
     * @var ZoneRepositoryInterface
     */
    protected $zoneRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $zoneCollectionFactory
     * @param ZoneFactory $zoneFactory
     * @param ZoneRepositoryInterface $zoneRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $zoneCollectionFactory,
        ZoneFactory $zoneFactory,
        ZoneRepositoryInterface $zoneRepository
    ) {
        parent::__construct($context);
        $this->zoneCollectionFactory = $zoneCollectionFactory;
        $this->filter = $filter;
        $this->zoneFactory = $zoneFactory;
        $this->zoneRepository = $zoneRepository;
    }

    /**
     * Update zone's is active status
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->zoneCollectionFactory->create());
            $updatedZonesCount = 0;
            foreach ($collection->getAllIds() as $zoneId) {
                $zone = $this->zoneRepository->getById($zoneId);
                $zone->setData('is_active', $this->getRequest()->getParam('is_active'));
                $this->zoneRepository->save($zone);
                $updatedZonesCount++;
            }

            if ($updatedZonesCount) {
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) were updated.', $updatedZonesCount)
                );
            }

            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory
                ->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('digidirect_extendedshippingrates/extendedshippingrates_zone/index');

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
        return $this->_authorization->isAllowed('Digidirect_ExtendedShippingRates::zone');
    }
}
