<?php

declare(strict_types=1);

namespace Digidirect\CouponTrack\Controller\Index;

use Magento\Framework\App\ActionInterface;

class Index implements ActionInterface {

    public function execute() {
        die('Test module');
        // return $this->resultFactory->create()->setContents('Hello This my First Content');
    }
}