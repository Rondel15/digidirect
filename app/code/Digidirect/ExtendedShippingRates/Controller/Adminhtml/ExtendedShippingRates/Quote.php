<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates;

abstract class Quote extends \Magento\Backend\App\Action
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
     * @var \Digidirect\ExtendedShippingRates\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var \Digidirect\ExtendedShippingRates\Api\RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Digidirect\ExtendedShippingRates\Model\RuleFactory $ruleFactory
     * @param \Digidirect\ExtendedShippingRates\Api\RuleRepositoryInterface $ruleRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Digidirect\ExtendedShippingRates\Model\RuleFactory $ruleFactory,
        \Digidirect\ExtendedShippingRates\Api\RuleRepositoryInterface $ruleRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->fileFactory = $fileFactory;
        $this->dateFilter = $dateFilter;
        $this->ruleFactory = $ruleFactory;
        $this->ruleRepository = $ruleRepository;
        $this->logger = $logger;
    }

    /**
     * Initiate rule
     *
     * @return void
     */
    protected function _initRule()
    {
        $rule = $this->ruleFactory->create();
        $this->coreRegistry->register(
            \Digidirect\ExtendedShippingRates\Model\Rule::CURRENT_PROMO_QUOTE_RULE,
            $rule
        );
        $id = (int)$this->getRequest()->getParam('id');

        if (!$id && $this->getRequest()->getParam('rule_id')) {
            $id = (int)$this->getRequest()->getParam('rule_id');
        }

        if ($id) {
            $this->coreRegistry->unregister(\Digidirect\ExtendedShippingRates\Model\Rule::CURRENT_PROMO_QUOTE_RULE);
            $this->coreRegistry->register(
                \Digidirect\ExtendedShippingRates\Model\Rule::CURRENT_PROMO_QUOTE_RULE,
                $this->ruleRepository->getById($id)
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
        $this->_setActiveMenu('Digidirect_ExtendedShippingRates::extendedshippingrates_quote')
            ->_addBreadcrumb(__('Shipping Rules'), __('Shipping Rules'));
        return $this;
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
