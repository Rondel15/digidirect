<?php
namespace Digidirect\AbstractGiftCard\Model\Config\Source;

/**
 * Class SslVersion
 * @package Digidirect\AbstractGiftCard\Model\Config\Source
 */
class SslVersion implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => -1, 'label' => __('Disable')],
            ['value' => CURL_SSLVERSION_DEFAULT, 'label' => __('Default')],
            ['value' => CURL_SSLVERSION_TLSv1, 'label' => __('TLSv1.x')],
            ['value' => CURL_SSLVERSION_SSLv2, 'label' => __('SSLv2')],
            ['value' => CURL_SSLVERSION_SSLv3, 'label' => __('SSLv3')],
            ['value' => CURL_SSLVERSION_TLSv1_0, 'label' => __('TLSv1.0')],
            ['value' => CURL_SSLVERSION_TLSv1_1, 'label' => __('TLSv1.1')],
            ['value' => CURL_SSLVERSION_TLSv1_2, 'label' => __('TLSv1.2')]
        ];
    }
}
