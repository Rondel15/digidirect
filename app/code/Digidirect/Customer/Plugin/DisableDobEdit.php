<?php

namespace Digidirect\Customer\Plugin;

use Magento\Customer\Block\Widget\Dob;
use Magento\Framework\Exception\NoSuchEntityException;

class DisableDobEdit {
    /**
     * Add disabled attribute to Dob field in case Customer already set the Dob value.
     *
     * @param Dob $subject
     * @param string $result
     * @return string
     */
    public function afterGetHtmlExtraParams(Dob $subject, string $result): string
    {
        try {
            if ($subject->getRequest()->getActionName() !== 'create' && $subject->getData('value')) {
                $result .= ' disabled';
            }
        } catch (NoSuchEntityException $e) {
            // do nothing
        }

        return $result;
    }
}