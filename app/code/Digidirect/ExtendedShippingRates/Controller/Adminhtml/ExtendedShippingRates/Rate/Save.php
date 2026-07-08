<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Rate;

class Save extends \Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Rate
{
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Digidirect\ExtendedShippingRates\Model\Carrier\Method\RateFactory $rateFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Digidirect\ExtendedShippingRates\Api\RateRepositoryInterface $rateRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Digidirect\ExtendedShippingRates\Model\Carrier\Method\RateFactory $rateFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Psr\Log\LoggerInterface $logger,
        \Digidirect\ExtendedShippingRates\Api\RateRepositoryInterface $rateRepository
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $dateFilter,
            $rateFactory,
            $rateRepository,
            $logger
        );
        $this->regionFactory = $regionFactory;
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
            /** @var $model \Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate */
            $model = $this->rateFactory->create();
            $this->_eventManager->dispatch(
                'adminhtml_controller_digidirect_extendedshippingrates_rate_prepare_save',
                ['request' => $this->getRequest()]
            );
            $data = $this->getRequest()->getPostValue();
            $inputFilter = new \Magento\Framework\Filter\FilterInput(
                ['created_at' => $this->dateFilter, 'updated_at' => $this->dateFilter],
                [],
                $data
            );
            $data = $inputFilter->getUnescaped();
            $id = $this->getRequest()->getParam('rate_id');
            if ($id) {
                $model = $this->rateRepository->getById($id);
            }

            $data = $this->prepareData($data);
            $validateResult = $model->validateData($this->dataObjectFactory->create(['data' => $data]));
            if ($validateResult !== true) {
                foreach ($validateResult as $errorMessage) {
                    $this->messageManager->addErrorMessage($errorMessage);
                }
                $this->_session->setPageData($data);
                $this->_redirect('digidirect_extendedshippingrates/*/edit', ['id' => $model->getData('rate_id')]);

                return;
            }

            $model->addData($data);
            $model->unsetData('updated_at');
            $this->_session->setPageData($model->getData());
            $this->rateRepository->save($model);
            $this->messageManager->addSuccessMessage(__('You saved the rate.'));
            $this->_session->setPageData(false);
            if ($this->isBackToMethod($data)) {
                $this->_redirect(
                    'digidirect_extendedshippingrates/extendedshippingrates_method/edit',
                    ['id' => $model->getData('method_id')]
                );

                return;
            }
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('digidirect_extendedshippingrates/*/edit', ['id' => $model->getData('rate_id')]);

                return;
            }
            $this->_redirect('digidirect_extendedshippingrates/*/');

            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $id = (int)$this->getRequest()->getParam('rate_id');
            if (!empty($id)) {
                $this->_redirect('digidirect_extendedshippingrates/*/edit', ['id' => $id]);
            } else {
                $this->_redirect('digidirect_extendedshippingrates/*/new');
            }

            return;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the rate data. Please review the error log.')
            );
            $this->logger->critical($e);
            $data = !empty($data) ? $data : [];
            $this->_session->setPageData($data);
            $this->_redirect('digidirect_extendedshippingrates/*/edit', ['id' => $this->getRequest()->getParam('rate_id')]);

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
        if (empty($data['rate_id'])) {
            $data['rate_id'] = null;
        }

        if (!empty($data['region_id'])) {
            /** @var \Magento\Directory\Model\Region $region */
            $region = $this->regionFactory->create();
            $region->getResource()->load($region, $data['region_id']);
            $data['region'] = $region->getCode();
        }

        unset($data['created_at']);
        unset($data['updated_at']);

        return $data;
    }
}
