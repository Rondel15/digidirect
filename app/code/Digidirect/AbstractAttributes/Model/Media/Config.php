<?php
namespace Digidirect\AbstractAttributes\Model\Media;

/**
 * Class Config
 * @package Magento\Catalog\Model\Product\Media
 */
class Config extends \Magento\Catalog\Model\Product\Media\Config
{
    const VALID_TYPES = ['gif', 'jpeg', 'jpg', 'png'];

    const MEDIA_URL = 'eaa/option/images';
    const MEDIA_PATH = 'eaa/option/images';

    /**
     * {@inheritdoc}
     */
    public function getBaseMediaPathAddition()
    {
        return self::MEDIA_PATH;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseMediaUrlAddition()
    {
        return self::MEDIA_URL;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseMediaPath()
    {
        return self::MEDIA_PATH;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
               . self::MEDIA_URL;
    }
}
