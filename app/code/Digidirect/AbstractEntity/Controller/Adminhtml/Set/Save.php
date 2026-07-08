<?php

namespace Digidirect\AbstractEntity\Controller\Adminhtml\Set;

use Digidirect\AbstractEntity\Model\Registry\Constants;
use Digidirect\AbstractEntity\Controller\Adminhtml\Set;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity as AbstractEntityResource;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Serialize\Serializer\Json as JsonHelper;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends Set
{
    const ADMIN_RESOURCE = 'Digidirect_AbstractEntity::abstractentity_save';

    const ADD_ACTION = 'digidirect_abstractentity/*/add';
    const EDIT_ACTION = 'digidirect_abstractentity/*/edit';
    const BACK_ACTION = 'digidirect_abstractentity/*/';

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $typeList;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param AbstractEntityResource $abstractEntityResource
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\Cache\TypeListInterface $typeList
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        AbstractEntityResource $abstractEntityResource,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        TypeListInterface $typeList,
        array $data = []
    ) {
        parent::__construct($context, $coreRegistry, $abstractEntityResource);
        $this->layoutFactory = $layoutFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->typeList = $typeList;
        $this->data = $data;
    }

    /**
     * Retrieve catalog product entity type id
     *
     * @return int
     */
    protected function _getEntityTypeId()
    {
        if ($this->_coreRegistry->registry(Constants::ENTITY_TYPE) === null) {
            $this->_setTypeId();
        }
        return $this->_coreRegistry->registry(Constants::ENTITY_TYPE);
    }

    /**
     * Save attribute set action
     *
     * [POST] Create attribute set from another set and redirect to edit page
     * [AJAX] Save attribute set data
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $entityTypeId = $this->_getEntityTypeId();
        $hasError = false;
        $attributeSetId = $this->getRequest()->getParam('id', false);
        $isNewSet = $this->getRequest()->getParam('gotoEdit', false) == '1';

        /* @var $model \Magento\Eav\Model\Entity\Attribute\Set */
        $model = $this->_objectManager->create(AttributeSet::class)
            ->setEntityTypeId($entityTypeId);

        /** @var $filterManager \Magento\Framework\Filter\FilterManager */
        $filterManager = $this->_objectManager->get(FilterManager::class);

        try {
            if ($isNewSet) {
                //filter html tags
                $name = $filterManager->stripTags($this->getRequest()->getParam('attribute_set_name'));
                $model->setAttributeSetName(trim($name));
            } else {
                if ($attributeSetId) {
                    $model->getResource()->load($model, $attributeSetId);
                }
                if (!$model->getId()) {
                    throw new LocalizedException(__('This attribute set no longer exists.'));
                }

                $data = $this->_objectManager->get(JsonHelper::class)
                    ->unserialize($this->getRequest()->getPost('data'));

                //filter html tags
                $data['attribute_set_name'] = $filterManager->stripTags($data['attribute_set_name']);
                $model->setUrlKey($filterManager->stripTags($data['url_key']));
                $model->organizeData($data);
                $model->setOrganizedData($data);
            }

            $model->validate();
            if ($isNewSet) {
                $model->getResource()->save($model);
                $model->initFromSkeleton($this->getRequest()->getParam('skeleton_set'));
            }
            $model->getResource()->save($model);
            $this->messageManager->addSuccessMessage(__('You saved the entity. Please clear invalid cache types.'));
            $this->typeList->invalidate($this->data['invalidate_cache_types'] ?? []);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $hasError = true;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
            $hasError = true;
        }

        if ($isNewSet) {
            if ($this->getRequest()->getPost('return_session_messages_only')) {
                /** @var $block \Magento\Framework\View\Element\Messages */
                $block = $this->layoutFactory->create()->getMessagesBlock();
                $block->setMessages($this->messageManager->getMessages(true));
                $body = [
                    'messages' => $block->getGroupedHtml(),
                    'error' => $hasError,
                    'id' => $model->getId(),
                ];
                return $this->resultJsonFactory->create()->setData($body);
            } else {
                $resultRedirect = $this->resultRedirectFactory->create();
                if ($hasError) {
                    $resultRedirect->setPath(static::ADD_ACTION);
                } else {
                    $resultRedirect->setPath(static::EDIT_ACTION, ['id' => $model->getId()]);
                }
                return $resultRedirect;
            }
        } else {
            $response = [];
            if ($hasError) {
                $layout = $this->layoutFactory->create();
                $layout->initMessages();
                $response['error'] = 1;
                $response['message'] = $layout->getMessagesBlock()->getGroupedHtml();
            } else {
                $response['error'] = 0;
                $response['url'] = $this->getUrl(static::BACK_ACTION);
            }
            return $this->resultJsonFactory->create()->setData($response);
        }
    }
}
