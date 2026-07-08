<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates;

use Digidirect\ExtendedShippingRates\Model\Zone as ZoneModel;

abstract class Zone extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Digidirect\ExtendedShippingRates\Model\ZoneFactory
     */
    protected $zoneFactory;

    /**
     * @var \Digidirect\ExtendedShippingRates\Api\ZoneRepositoryInterface
     */
    protected $zoneRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Digidirect\ExtendedShippingRates\Model\ZoneFactory $zoneFactory
     * @param \Digidirect\ExtendedShippingRates\Api\ZoneRepositoryInterface $zoneRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Digidirect\ExtendedShippingRates\Model\ZoneFactory $zoneFactory,
        \Digidirect\ExtendedShippingRates\Api\ZoneRepositoryInterface $zoneRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->zoneFactory = $zoneFactory;
        $this->zoneFactory = $zoneFactory;
        $this->zoneRepository = $zoneRepository;
        $this->logger = $logger;
    }

    /**
     * Initiate zone
     *
     * @return void
     */
    protected function _initZone()
    {
        $zone = $this->zoneFactory->create();
        $this->coreRegistry->register(
            ZoneModel::CURRENT_ZONE,
            $zone
        );
        $id = (int)$this->getRequest()->getParam('id');

        if (!$id && $this->getRequest()->getParam('entity_id')) {
            $id = (int)$this->getRequest()->getParam('entity_id');
        }

        if ($id) {
            $this->coreRegistry->unregister(ZoneModel::CURRENT_ZONE);
            $this->coreRegistry->register(
                ZoneModel::CURRENT_ZONE,
                $this->zoneRepository->getById($id)
            );
        }
    }

    /**
     * Initiate action
     *
     * @return Zone
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();

        return $this;
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
