<?php

declare(strict_types=1);

namespace Digidirect\Digi\Block\CustomAttributeManagement\Form\Renderer;

use Magento\CustomAttributeManagement\Block\Form\Renderer\Text;

/**
 * Class AIPPNumber
 *
 * @author Michael Marchanka <michail.marchenko@Digidirect.com>
 */
class AIPPNumber extends Text
{
    protected const DEPENDING_ATTR_CODE = 'is_aipp_verified';

    /**
     * @return bool
     */
    public function isAIPPVerified(): bool
    {
        return (bool) $this->getEntity()->getData(static::DEPENDING_ATTR_CODE);
    }

    /**
     * @return string
     */
    public function getFilteredValue(): string
    {
        if ($this->isAIPPVerified()) {
            return $this->getEscapedValue();
        }

        return '';
    }
}
