<?php

namespace Digidirect\Feed\Controller\Adminhtml\Dynamic\Category;

use Magento\Framework\Controller\ResultFactory;
use Digidirect\Feed\Controller\Adminhtml\Dynamic\Category;

class Index extends Category
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->_initPage($resultPage);

        return $resultPage;
    }
}
