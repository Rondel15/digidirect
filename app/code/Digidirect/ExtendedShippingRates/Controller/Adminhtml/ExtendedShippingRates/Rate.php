<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates;

use Magento\Backend\App\Action;

abstract class Rate extends Action
{
    const BACK_TO_PARAM = 'back_to';
    const BACK_TO_METHOD_PARAM = 'to_method';

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
     * @var \Digidirect\ExtendedShippingRates\Model\Carrier\Method\RateFactory
     */
    protected $rateFactory;

    /**
     * @var \Digidirect\ExtendedShippingRates\Api\RateRepositoryInterface
     */
    protected $rateRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Digidirect\ExtendedShippingRates\Model\Carrier\Method\RateFactory $rateFactory
     * @param \Digidirect\ExtendedShippingRates\Api\RateRepositoryInterface $rateRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Digidirect\ExtendedShippingRates\Model\Carrier\Method\RateFactory $rateFactory,
        \Digidirect\ExtendedShippingRates\Api\RateRepositoryInterface $rateRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->fileFactory = $fileFactory;
        $this->dateFilter = $dateFilter;
        $this->rateFactory = $rateFactory;
        $this->rateFactory = $rateFactory;
        $this->rateRepository = $rateRepository;
        $this->logger = $logger;
    }

    /**
     * Initiate rate
     *
     * @return void
     */
    protected function _init()
    {
        /** @var \Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate $rate */
        $rate = $this->rateFactory->create();
        $this->coreRegistry->register(
            \Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate::CURRENT_RATE,
            $rate
        );
        $id = (int)$this->getRequest()->getParam('id');

        if (!$id && $this->getRequest()->getParam('rate_id')) {
            $id = (int)$this->getRequest()->getParam('rate_id');
        }

        if ($id) {
            $this->coreRegistry->unregister(\Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate::CURRENT_RATE);
            $this->coreRegistry->register(
                \Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate::CURRENT_RATE,
                $this->rateRepository->getById($id)
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
     * Check: whether it is necessary to redirect the administrator to the method-edit page
     *
     * @param array $data
     * @return bool
     */
    public function isBackToMethod($data = [])
    {
        if ($this->getRequest()->getParam(static::BACK_TO_PARAM) == static::BACK_TO_METHOD_PARAM) {
            return true;
        }

        if (isset($data[static::BACK_TO_PARAM]) && $data[static::BACK_TO_PARAM] == static::BACK_TO_METHOD_PARAM) {
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
