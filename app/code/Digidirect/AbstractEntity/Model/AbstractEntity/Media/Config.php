<?php
namespace Digidirect\AbstractEntity\Model\AbstractEntity\Media;

use Magento\Framework\UrlInterface;

class Config extends \Magento\Catalog\Model\Product\Media\Config
{
    const VALID_TYPES = ['gif', 'jpeg', 'jpg', 'png'];

    const MEDIA_URL = 'digidirect/abstractentity/images';
    const MEDIA_PATH = 'digidirect/abstractentity/images';

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
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . self::MEDIA_URL;
    }
}
