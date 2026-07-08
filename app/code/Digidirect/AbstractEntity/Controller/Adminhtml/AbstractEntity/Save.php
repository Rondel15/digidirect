<?php
namespace Digidirect\AbstractEntity\Controller\Adminhtml\AbstractEntity;

use Digidirect\AbstractEntity\Controller\Adminhtml\AbstractEntity as AbstractEntityController;
use Digidirect\AbstractEntity\Model\AbstractEntity;
use Digidirect\AbstractEntity\Api\AbstractEntityRepositoryInterface;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Model\Registry\Constants;
use Digidirect\AbstractEntity\Model\DataFilterPool;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Registry;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends AbstractEntityController
{
    const ADMIN_RESOURCE_PREFIX = 'Digidirect_AbstractEntity::abstractentity_record_save_';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var AbstractEntityRepositoryInterface
     */
    protected $abstractEntityRepository;

    /**
     * @var DataFilterPool
     */
    protected $dataFilterPool;

    /**
     * Save constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param AbstractEntityRepositoryInterface $abstractEntityRepository
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param DataPersistorInterface $dataPersistor
     * @param DataFilterPool $dataFilterPool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        AbstractEntityRepositoryInterface $abstractEntityRepository,
        AttributeSetRepositoryInterface $attributeSetRepository,
        DataPersistorInterface $dataPersistor,
        DataFilterPool $dataFilterPool
    ) {
        $this->abstractEntityRepository = $abstractEntityRepository;
        $this->dataPersistor = $dataPersistor;
        $this->dataFilterPool = $dataFilterPool;
        parent::__construct(
            $context,
            $coreRegistry,
            $resultPageFactory,
            $resultForwardFactory,
            $abstractEntityRepository,
            $attributeSetRepository
        );
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        $setId = (int)$this->getRequest()->getParam(AbstractEntityInterface::ATTRIBUTE_SET_ID);

        if ($data) {
            $id = $this->getRequest()->getParam('entity_id');
            $storeId = (int)$this->getRequest()->getParam('store_id');

            try {
                if ($id) {
                    $model = $this->abstractEntityRepository->getById($id);
                } else {
                    $model = $this->_objectManager->create(AbstractEntity::class);
                }

                $data = $this->_filterPostData($data);
                $model->setData($data);

                if (isset($data['use_default']) && !empty($data['use_default'])) {
                    foreach ($data['use_default'] as $attributeCode => $attributeValue) {
                        if ($attributeValue) {
                            $model->setData($attributeCode, null);
                        }
                    }
                }

                $this->abstractEntityRepository->save($model);
                $this->_eventManager->dispatch('adminhtml_digidirect_abstractentity_save_after', [
                    'entity' => $model
                ]);

                $this->messageManager->addSuccessMessage(__('You saved the record.'));
                $this->dataPersistor->clear(Constants::CURRENT_ABSTRACT_ENTITY);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [
                        'id' => $model->getId(),
                        'store' => $storeId,
                        AbstractEntityInterface::ATTRIBUTE_SET_ID => $setId
                    ]);
                }
                return $resultRedirect->setPath('*/*/', [AbstractEntityInterface::ATTRIBUTE_SET_ID => $setId]);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving.'));
            }

            $this->dataPersistor->set(Constants::CURRENT_ABSTRACT_ENTITY, $data);
            return $resultRedirect->setPath('*/*/edit', [
                'id' => $id,
                'store' => $storeId,
                AbstractEntityInterface::ATTRIBUTE_SET_ID => $setId
            ]);
        }
        return $resultRedirect->setPath('*/*/', [AbstractEntityInterface::ATTRIBUTE_SET_ID => $setId]);
    }

    /**
     * Data preprocessing
     * @param array $data
     * @return array
     * @throws LocalizedException
     */
    protected function _filterPostData($data)
    {
        return $this->dataFilterPool->execute($data);
    }
}
