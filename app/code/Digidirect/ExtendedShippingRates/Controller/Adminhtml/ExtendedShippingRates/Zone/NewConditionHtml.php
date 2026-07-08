<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Zone;

use Digidirect\ExtendedShippingRates\Ui\DataProvider\Zone\Form\Modifier\AbstractModifier;

class NewConditionHtml extends \Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Zone
{
    /**
     * New condition html action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = $this->_objectManager->create(
            $type
        )->setId(
            $id
        )->setType(
            $type
        )->setRule(
            $this->zoneFactory->create()
        )->setPrefix(
            'conditions'
        )->setFormName(
            AbstractModifier::FORM_NAME
        );
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof \Magento\Rule\Model\Condition\AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
}
