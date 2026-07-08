<?php

namespace Digidirect\Utilities\Model\Media;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;

class Config extends \Magento\Framework\DataObject implements ConfigInterface
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Config constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($data);
    }

    /**
     * @return mixed
     */
    protected function _getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Retrieve base url for media files
     *
     * @return string
     */
    public function getBaseMediaUrl()
    {
        return $this->_getBaseUrl() . $this->getData('baseMediaUrl');
    }

    /**
     * Retrieve base path for media files
     *
     * @return string
     */
    public function getBaseMediaPath()
    {
        return $this->getData('baseMediaPath');
    }

    /**
     * Retrieve url for media file
     *
     * @param string $file
     * @return string
     */
    public function getMediaUrl($file)
    {
        return $this->_getBaseUrl() . $this->getData('mediaUrl');
    }

    /**
     * Retrieve file system path for media file
     *
     * @param string $file
     * @return string
     */
    public function getMediaPath($file)
    {
        return $this->getData('mediaPath');
    }
}
