<?php

namespace Digidirect\Feed\Controller\Adminhtml\Template;

use Magento\Framework\Controller\ResultFactory;
use Digidirect\Feed\Controller\Adminhtml\Template;

class Index extends Template
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
