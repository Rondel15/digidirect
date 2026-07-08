<?php

namespace Digidirect\Feed\Controller\Adminhtml\Rule;

use Digidirect\Feed\Controller\Adminhtml\Rule;

class Save extends Rule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getParams()) {
            try {
                $model = $this->initModel();
                $model->addData($data['data']);

                if (isset($data['rule'])) {
                    $model->loadPost($data['rule']);
                }

                $this->ruleRepository->save($model);

                $this->messageManager->addSuccessMessage(__('Filter was successfully saved'));

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong while trying to save the rule.'));
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        } else {
            $this->messageManager->addErrorMessage(__('Unable to find item to save'));

            return $resultRedirect->setPath('*/*/');
        }
    }
}
