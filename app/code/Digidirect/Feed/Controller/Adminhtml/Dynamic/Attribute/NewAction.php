<?php

namespace Digidirect\Feed\Controller\Adminhtml\Dynamic\Attribute;

use Digidirect\Feed\Controller\Adminhtml\Dynamic\Attribute as DynamicAttribute;

class NewAction extends DynamicAttribute
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        return $this->resultForwardFactory->create()->forward('edit');
    }
}
