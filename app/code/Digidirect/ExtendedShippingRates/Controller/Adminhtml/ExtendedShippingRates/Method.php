<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates;

use Magento\Backend\App\Action;

abstract class Method extends Action
{
    const BACK_TO_PARAM = 'back_to';
    const BACK_TO_CARRIER_PARAM = 'to_carrier';

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
     * @var \Digidirect\ExtendedShippingRates\Model\Carrier\MethodFactory
     */
    protected $methodFactory;

    /**
     * @var \Digidirect\ExtendedShippingRates\Api\MethodRepositoryInterface
     */
    protected $methodRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Digidirect\ExtendedShippingRates\Model\Carrier\MethodFactory $methodFactory
     * @param \Digidirect\ExtendedShippingRates\Api\MethodRepositoryInterface $methodRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Digidirect\ExtendedShippingRates\Model\Carrier\MethodFactory $methodFactory,
        \Digidirect\ExtendedShippingRates\Api\MethodRepositoryInterface $methodRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->fileFactory = $fileFactory;
        $this->dateFilter = $dateFilter;
        $this->methodFactory = $methodFactory;
        $this->methodRepository = $methodRepository;
        $this->logger = $logger;
    }

    /**
     * Initiate method
     *
     * @return void
     */
    protected function _init()
    {
        /** @var \Digidirect\ExtendedShippingRates\Model\Carrier\Method $method */
        $method = $this->methodFactory->create();
        $this->coreRegistry->register(
            \Digidirect\ExtendedShippingRates\Model\Carrier\Method::CURRENT_METHOD,
            $method
        );
        $id = (int)$this->getRequest()->getParam('id');

        if (!$id && $this->getRequest()->getParam('entity_id')) {
            $id = (int)$this->getRequest()->getParam('entity_id');
        }

        if ($id) {
            $this->coreRegistry->unregister(\Digidirect\ExtendedShippingRates\Model\Carrier\Method::CURRENT_METHOD);
            $this->coreRegistry->register(
                \Digidirect\ExtendedShippingRates\Model\Carrier\Method::CURRENT_METHOD,
                $this->methodRepository->getById($id)
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
     * Check: whether it is necessary to redirect the administrator to the carrier-edit page
     *
     * @param array $data
     * @return bool
     */
    public function isBackToCarrier($data = [])
    {
        if ($this->getRequest()->getParam(static::BACK_TO_PARAM) == static::BACK_TO_CARRIER_PARAM) {
            return true;
        }

        if (isset($data[static::BACK_TO_PARAM]) && $data[static::BACK_TO_PARAM] == static::BACK_TO_CARRIER_PARAM) {
            return true;
        }

        return false;
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
