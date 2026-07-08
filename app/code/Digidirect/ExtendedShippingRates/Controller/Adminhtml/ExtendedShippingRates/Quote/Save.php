<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Quote;

use Magento\Store\Model\Store;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Save
 * @package Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Quote
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Quote
{
    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Digidirect\ExtendedShippingRates\Model\RuleFactory $ruleFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Digidirect\ExtendedShippingRates\Api\RuleRepositoryInterface $ruleRepository
     * @param Json $jsonSerializer
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Digidirect\ExtendedShippingRates\Model\RuleFactory $ruleFactory,
        \Psr\Log\LoggerInterface $logger,
        \Digidirect\ExtendedShippingRates\Api\RuleRepositoryInterface $ruleRepository,
        Json $jsonSerializer
    ) {
        $this->jsonSerializer = $jsonSerializer;
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $dateFilter,
            $ruleFactory,
            $ruleRepository,
            $logger
        );
    }

    /**
     * Promo quote save action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        if (!$this->getRequest()->getPostValue()) {
            $this->_redirect('digidirect_extendedshippingrates/*/');
        }

        try {
            /** @var $model \Digidirect\ExtendedShippingRates\Model\Rule */
            $model = $this->ruleFactory->create();
            $this->_eventManager->dispatch(
                'adminhtml_controller_extendedshippingrates_prepare_save',
                ['request' => $this->getRequest()]
            );
            $data = $this->getRequest()->getPostValue();
            $inputFilter = new \Magento\Framework\Filter\FilterInput(
                ['from_date' => $this->dateFilter, 'to_date' => $this->dateFilter],
                [],
                $data
            );
            $data = $inputFilter->getUnescaped();
            $id = $this->getRequest()->getParam('rule_id');
            if ($id) {
                $model = $this->ruleRepository->getById($id);
            }

            $validateResult = $model->validateData(new \Magento\Framework\DataObject($data));
            if ($validateResult !== true) {
                foreach ($validateResult as $errorMessage) {
                    $this->messageManager->addErrorMessage($errorMessage);
                }
                $this->_session->setPageData($data);
                $this->_redirect('digidirect_extendedshippingrates/*/edit', ['id' => $model->getId()]);
                return;
            }

            $data = $this->prepareData($data, $model);
            $model->loadPost($data);

            $this->_session->setPageData($model->getData());

            $this->ruleRepository->save($model);
            $this->messageManager->addSuccessMessage(__('You saved the rule.'));
            $this->_session->setPageData(false);
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('digidirect_extendedshippingrates/*/edit', ['id' => $model->getId()]);
                return;
            }
            $this->_redirect('digidirect_extendedshippingrates/*/');
            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $id = (int)$this->getRequest()->getParam('rule_id');
            if (!empty($id)) {
                $this->_redirect('digidirect_extendedshippingrates/*/edit', ['id' => $id]);
            } else {
                $this->_redirect('digidirect_extendedshippingrates/*/new');
            }
            return;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the rule data. Please review the error log.')
            );
            $this->logger->critical($e);
            $data = !empty($data) ? $data : [];
            $this->_session->setPageData($data);
            $this->_redirect('digidirect_extendedshippingrates/*/edit', ['id' => $this->getRequest()->getParam('rule_id')]);
            return;
        }
    }

    /**
     * Prepares specific data
     *
     * @param array $data
     * @param \Digidirect\ExtendedShippingRates\Model\Rule $model
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function prepareData($data, \Digidirect\ExtendedShippingRates\Model\Rule $model)
    {
        foreach ($model->getResource()->getSerializedFields() as $fieldKey => $fieldValue) {
            if (isset($data[$fieldKey]) && !is_array($data[$fieldKey])) {
                $data[$fieldKey] = $this->jsonSerializer->unserialize($data[$fieldKey]);
            }
        }

        if ($data['rule_id'] == '') {
            $data['rule_id'] = null;
        }

        if (!empty($data['simple_action']) && is_array($data['simple_action'])) {
            $data['simple_action'] = implode(',', $data['simple_action']);
        }

        if (!empty($data['days_of_week'])) {
            $data['days_of_week'] = implode(',', $data['days_of_week']);
        } else {
            $data['days_of_week'] = null;
        }

        if (isset($data['rule']['conditions'])) {
            $data['conditions'] = $data['rule']['conditions'];
        }

        if (isset($data['rule']['actions'])) {
            $data['actions'] = $data['rule']['actions'];
        }
        unset($data['rule']);

        if (array_search(Store::DEFAULT_STORE_ID, $data['store_id']) !== false) {
            $data['store_id'] = [Store::DEFAULT_STORE_ID];
        }

        if (!empty($data['store_id'])) {
            $data['store_ids'] = $data['store_id'];
        }

        if (!isset($data['use_time'])) {
            $data['use_time'] = 0;
        }

        return $data;
    }
}
