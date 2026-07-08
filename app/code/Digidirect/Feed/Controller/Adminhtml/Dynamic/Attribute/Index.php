<?php

namespace Digidirect\Feed\Controller\Adminhtml\Dynamic\Attribute;

use Magento\Framework\Controller\ResultFactory;
use Digidirect\Feed\Controller\Adminhtml\Dynamic\Attribute as DynamicAttribute;

class Index extends DynamicAttribute
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page\Interceptor $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($resultPage);

        return $resultPage;
    }
}
