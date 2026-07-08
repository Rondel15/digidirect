<?php

namespace Digidirect\Feed\Controller\Adminhtml\Rule;

use Digidirect\Feed\Controller\Adminhtml\Rule;

class NewAction extends Rule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        return $this->resultForwardFactory->create()->forward('edit');
    }
}
