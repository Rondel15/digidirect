<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Zone;

use Magento\Store\Model\Store;

/**
 * Class Save
 * @package Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Zone
 */
class Save extends \Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Zone
{
    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $dataObjectFactory;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\DataObject\Factory $dataObjectFactory
     * @param \Digidirect\ExtendedShippingRates\Model\ZoneFactory $zoneFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Digidirect\ExtendedShippingRates\Api\ZoneRepositoryInterface $zoneRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\DataObject\Factory $dataObjectFactory,
        \Digidirect\ExtendedShippingRates\Model\ZoneFactory $zoneFactory,
        \Psr\Log\LoggerInterface $logger,
        \Digidirect\ExtendedShippingRates\Api\ZoneRepositoryInterface $zoneRepository
    ) {
        parent::__construct($context, $coreRegistry, $zoneFactory, $zoneRepository, $logger);
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Shipping zone save action
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
            /** @var $model \Digidirect\ExtendedShippingRates\Model\Zone */
            $model = $this->zoneFactory->create();
            $this->_eventManager->dispatch(
                'adminhtml_controller_extendedshippingrates_zone_prepare_save',
                ['request' => $this->getRequest()]
            );
            $data = $this->getRequest()->getPostValue();
            $id = $this->getRequest()->getParam('entity_id');
            if ($id) {
                $model = $this->zoneRepository->getById($id);
            }

            $validateResult = $model->validateData($this->dataObjectFactory->create($data));
            if ($validateResult !== true) {
                foreach ($validateResult as $errorMessage) {
                    $this->messageManager->addErrorMessage($errorMessage);
                }
                $this->_session->setPageData($data);
                $this->_redirect('digidirect_extendedshippingrates/*/edit', ['id' => $model->getId()]);

                return;
            }

            $data = $this->prepareData($data);
            $model->loadPost($data);

            $this->_session->setPageData($model->getData());

            $this->zoneRepository->save($model);
            $this->messageManager->addSuccessMessage(__('You saved the zone.'));
            $this->_session->setPageData(false);
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('digidirect_extendedshippingrates/*/edit', ['id' => $model->getId()]);

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
                __('Something went wrong while saving the zone data. Please review the error log.')
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
        if ($data['entity_id'] == '') {
            $data['entity_id'] = null;
        }

        if (!empty($data['store_id'])) {
            $data['store_ids'] = $data['store_id'];
        }

        if (isset($data['rule']['conditions'])) {
            $data['conditions'] = $data['rule']['conditions'];
        }

        unset($data['rule']);

        /** @var $model \Digidirect\ExtendedShippingRates\Model\Zone */
        $model = $this->zoneFactory->create();
        if ($data['attribute_set'] == 1) {
            $model->createSimpleConditions($data);
        } else {
            $model->removeSimpleAttributeValues($data);
        }

        if (array_search(Store::DEFAULT_STORE_ID, $data['store_id']) !== false) {
            $data['store_id'] = [Store::DEFAULT_STORE_ID];
        }

        unset($data['created_at']);
        unset($data['updated_at']);

        return $data;
    }
}
