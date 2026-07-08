<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates;

use Magento\Backend\App\Action;

abstract class Carrier extends Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * @var \Digidirect\ExtendedShippingRates\Model\CarrierFactory
     */
    protected $carrierFactory;

    /**
     * @var \Digidirect\ExtendedShippingRates\Model\CarrierRepository
     */
    protected $carrierRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Digidirect\ExtendedShippingRates\Model\CarrierFactory $carrierFactory
     * @param \Digidirect\ExtendedShippingRates\Api\CarrierRepositoryInterface $carrierRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Digidirect\ExtendedShippingRates\Model\CarrierFactory $carrierFactory,
        \Digidirect\ExtendedShippingRates\Api\CarrierRepositoryInterface $carrierRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->fileFactory = $fileFactory;
        $this->dateFilter = $dateFilter;
        $this->carrierFactory = $carrierFactory;
        $this->carrierRepository = $carrierRepository;
        $this->logger = $logger;
    }

    /**
     * Initiate rule
     *
     * @return void
     */
    protected function _initCarrier()
    {
        /** @var \Digidirect\ExtendedShippingRates\Model\Carrier $carrier */
        $carrier = $this->carrierFactory->create();
        $this->coreRegistry->register(
            \Digidirect\ExtendedShippingRates\Model\Carrier::CURRENT_CARRIER,
            $carrier
        );
        $id = (int)$this->getRequest()->getParam('id');

        if (!$id && $this->getRequest()->getParam('carrier_id')) {
            $id = (int)$this->getRequest()->getParam('carrier_id');
        }

        if ($id) {
            $this->coreRegistry->unregister(\Digidirect\ExtendedShippingRates\Model\Carrier::CURRENT_CARRIER);
            $this->coreRegistry->register(
                \Digidirect\ExtendedShippingRates\Model\Carrier::CURRENT_CARRIER,
                $this->carrierRepository->getById($id)
            );
        }
    }

    /**
     * Initiate action
     *
     * @return Quote
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Digidirect_ExtendedShippingRates::extendedshippingrates_carrier')
            ->_addBreadcrumb(__('Carriers'), __('Carriers'));

        return $this;
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
