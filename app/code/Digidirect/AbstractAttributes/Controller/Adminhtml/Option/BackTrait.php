<?php
namespace Digidirect\AbstractAttributes\Controller\Adminhtml\Option;

/**
 * BackTrait
 * @package Digidirect\AbstractAttributes\Controller\Adminhtml\Option
 */
trait BackTrait
{
    /**
     * Result redirect
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function _resultRedirect()
    {
        $backId = $this->getRequest()->getParam('back_to_edit_attribute_id');
        if ($backId) {
            $path = 'catalog/product_attribute/edit';
            $params = [
                'attribute_id' => $backId,
                'active_tab'   => 'advanced_options_properties'
            ];
        } else {
            $path = '*/*/grid';
            $params = [];
        }

        return $this->resultRedirectFactory->create()->setPath($path, $params);
    }
}
