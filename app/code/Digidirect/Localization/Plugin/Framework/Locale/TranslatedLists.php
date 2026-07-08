<?php
namespace Digidirect\Localization\Plugin\Framework\Locale;

/**
 * Class TranslatedLists
 * @package Digidirect\Localization\Plugin\Framework\Locale
 */
class TranslatedLists
{
    /**
     * @param \Magento\Framework\Locale\TranslatedLists $subject
     * @param string $result
     * @return \Magento\Framework\Phrase
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCountryTranslation(\Magento\Framework\Locale\TranslatedLists $subject, $result)
    {
        if ($result !== null) {
            $result = __($result);
            return $result->__toString();
        }
        return $result;
    }
}
