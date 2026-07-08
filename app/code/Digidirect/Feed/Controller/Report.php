<?php

namespace Digidirect\Feed\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

abstract class Report extends Action
{
    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }
}
