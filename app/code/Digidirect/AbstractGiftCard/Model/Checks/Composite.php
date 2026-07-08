<?php

namespace Digidirect\AbstractGiftCard\Model\Checks;

use Digidirect\AbstractGiftCard\Model\ServiceInterface;
use Magento\Quote\Model\Quote;

class Composite implements SpecificationInterface
{
    /** @var SpecificationInterface[]  */
    protected $_list = [];

    /**
     * @param SpecificationInterface[] $list
     */
    public function __construct(array $list)
    {
        $this->_list = $list;
    }

    /**
     * Check whether service is applicable to quote
     *
     * @param ServiceInterface $service
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    public function isApplicable(ServiceInterface $service, Quote $quote)
    {
        foreach ($this->_list as $specification) {
            if (!$specification->isApplicable($service, $quote)) {
                return false;
            }
        }
        return true;
    }
}
