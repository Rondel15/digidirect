<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Popups;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Rule\Model\Condition\AbstractCondition;
use Plumrocket\Newsletterpopup\Controller\Adminhtml\Popups;
use Plumrocket\Newsletterpopup\Helper\Data;

class NewConditionHtml extends Popups
{

    /**
     * @var \Plumrocket\Newsletterpopup\Model\PopupFactory
     */
    protected $popupFactory;

    /**
     * @param \Magento\Backend\App\Action\Context            $context
     * @param \Plumrocket\Newsletterpopup\Helper\Data        $dataHelper
     * @param \Magento\Framework\App\ResourceConnection      $resource
     * @param \Plumrocket\Newsletterpopup\Model\PopupFactory $popupFactory
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        ResourceConnection $resource,
        \Plumrocket\Newsletterpopup\Model\PopupFactory $popupFactory
    ) {
        $this->popupFactory = $popupFactory;
        parent::__construct($context, $dataHelper, $resource);
    }

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
            $this->popupFactory->create()
        )->setPrefix(
            'conditions'
        );
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
}
