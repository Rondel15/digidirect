<?php

namespace Digidirect\Feed\Controller\Adminhtml\Dynamic\Attribute;

use Digidirect\Feed\Controller\Adminhtml\Dynamic\Attribute as DynamicAttribute;

class Save extends DynamicAttribute
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getParams()) {
            $model = $this->initModel();
            $data = $this->filterValues($data);
            $model->addData($data);

            try {
                $model->getResource()->save($model);

                $this->messageManager->addSuccessMessage(__('Item was successfully saved'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while trying to save the dynamic attribute.')
                );
                return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
            }
        } else {
            $this->messageManager->addErrorMessage(__('Unable to find item to save'));
            return $resultRedirect->setPath('*/*/');
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public function filterValues($data)
    {
        if (!isset($data['conditions'])) {
            $data['conditions'] = [];
        }

        $data['conditions'] = array_values($data['conditions']);

        return $data;
    }
}
