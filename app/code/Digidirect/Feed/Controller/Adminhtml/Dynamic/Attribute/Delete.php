<?php

namespace Digidirect\Feed\Controller\Adminhtml\Dynamic\Attribute;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Digidirect\Feed\Model\Dynamic\AttributeFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Digidirect\Feed\Controller\Adminhtml\Dynamic\Attribute as DynamicAttribute;

class Delete extends DynamicAttribute
{
    /**
     * Attribute constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ForwardFactory $resultForwardFactory
     * @param AttributeFactory $attributeFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        AttributeFactory $attributeFactory
    ) {
        $this->context = $context;
        $this->registry = $registry;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->attributeFactory = $attributeFactory;

        parent::__construct($context, $registry, $resultForwardFactory, $attributeFactory);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $model = $this->initModel();
            $model->getResource()->delete($model);

            $this->messageManager->addSuccessMessage(__('Item was successfully deleted'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while trying to delete the dynamic attribute.')
            );
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
