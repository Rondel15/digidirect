<?php

namespace Mbs\BestSeller;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class ProductSalesAttributeMissing extends LocalizedException
{
    /**
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     */
    public function __construct(Phrase $phrase = null, \Exception $cause = null, $code = 0)
    {
        if ($phrase === null) {
            $phrase = new Phrase('The product sales attributes has been removed');
        }
        parent::__construct($phrase, $cause, $code);
    }
}
