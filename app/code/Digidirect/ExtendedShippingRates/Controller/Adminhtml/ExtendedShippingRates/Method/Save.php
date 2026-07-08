<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Method;

use Digidirect\ExtendedShippingRates\Api\Data\MethodInterface;
use Digidirect\ExtendedShippingRates\Model\Config\Source\WeightType;

class Save extends \Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Method
{
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Digidirect\ExtendedShippingRates\Model\Carrier\MethodFactory $methodFactory
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Digidirect\ExtendedShippingRates\Api\MethodRepositoryInterface $methodRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Digidirect\ExtendedShippingRates\Model\Carrier\MethodFactory $methodFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Psr\Log\LoggerInterface $logger,
        \Digidirect\ExtendedShippingRates\Api\MethodRepositoryInterface $methodRepository
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $dateFilter,
            $methodFactory,
            $methodRepository,
            $logger
        );
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Method save action
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->getRequest()->getPostValue()) {
            $this->_redirect('digidirect_extendedshippingrates/*/');
        }

        try {
            /** @var $model \Digidirect\ExtendedShippingRates\Model\Carrier\Method */
            $model = $this->methodFactory->create();
            $this->_eventManager->dispatch(
                'adminhtml_controller_digidirect_extendedshippingrates_method_prepare_save',
                ['request' => $this->getRequest()]
            );
            $data = $this->getRequest()->getPostValue();
            $data['store_labels'] = $this->getRequest()->getPostValue('store_labels');
            $inputFilter = new \Magento\Framework\Filter\FilterInput(
                ['created_at' => $this->dateFilter, 'updated_at' => $this->dateFilter],
                [],
                $data
            );
            $data = $inputFilter->getUnescaped();
            $id = $this->getRequest()->getParam('entity_id');
            if ($id) {
                $model = $this->methodRepository->getById($id);
            }

            $data = $this->prepareData($data);
            $validateResult = $model->validateData($this->dataObjectFactory->create(['data' => $data]));
            if ($validateResult !== true) {
                foreach ($validateResult as $errorMessage) {
                    $this->messageManager->addErrorMessage($errorMessage);
                }
                $this->_session->setPageData($data);
                $this->_redirect('digidirect_extendedshippingrates/*/edit', ['id' => $model->getData('entity_id')]);

                return;
            }

            $model->loadPost($data);
            $model->addData($data);

            $this->_session->setPageData($model->getData());

            $this->methodRepository->save($model);
            $this->messageManager->addSuccessMessage(__('You saved the method.'));
            $this->_session->setPageData(false);
            if ($this->isBackToCarrier($data)) {
                $this->_redirect(
                    'digidirect_extendedshippingrates/extendedshippingrates_carrier/edit',
                    ['id' => $model->getData('carrier_id')]
                );

                return;
            }
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('digidirect_extendedshippingrates/*/edit', ['id' => $model->getData('entity_id')]);

                return;
            }
            $this->_redirect('digidirect_extendedshippingrates/*/');

            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $id = (int)$this->getRequest()->getParam('entity_id');
            if (!empty($id)) {
                $this->_redirect('digidirect_extendedshippingrates/*/edit', ['id' => $id]);
            } else {
                $this->_redirect('digidirect_extendedshippingrates/*/new');
            }

            return;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the method data. Please review the error log.')
            );
            $this->logger->critical($e);
            $data = !empty($data) ? $data : [];
            $this->_session->setPageData($data);
            $this->_redirect(
                'digidirect_extendedshippingrates/*/edit',
                ['id' => $this->getRequest()->getParam('entity_id')]
            );

            return;
        }
    }

    /**
     * Prepares specific data
     *
     * @param array $data
     * @return array
     */
    protected function prepareData($data)
    {
        if (!isset($data['entity_id']) || !$data['entity_id']) {
            $data['entity_id'] = null;
        }

        if (empty($data['code']) && !empty($data['title'])) {
            $code = mb_strtolower($data['title']);
            $code = preg_replace('/[^\da-z]/i', '', $code);
            $code = 'code' . $code;
            $data['code'] = $code;
        }

        if (WeightType::EMPTY_CODE == $data[MethodInterface::PACKAGING_WEIGHT_TYPE]) {
            $data[MethodInterface::PACKAGING_WEIGHT_TYPE] = '';
        }

        unset($data['created_at']);
        unset($data['updated_at']);

        return $data;
    }
}
