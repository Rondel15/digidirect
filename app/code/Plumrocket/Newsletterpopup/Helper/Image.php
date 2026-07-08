<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\ImageFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;

class Image extends AbstractHelper
{

    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    private $_store;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $_filesystem;

    /**
     * @var \Magento\Framework\ImageFactory
     */
    private $_imageFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context  $context
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param \Magento\Framework\Filesystem          $filesystem
     * @param \Magento\Framework\ImageFactory        $imageFactory
     */
    public function __construct(
        Context $context,
        StoreInterface $store,
        Filesystem $filesystem,
        ImageFactory $imageFactory
    ) {
        $this->_store = $store;
        $this->_filesystem = $filesystem;
        $this->_imageFactory = $imageFactory;
        parent::__construct($context);
    }

    /**
     * Resize image.
     * 
     * @param $url
     * @param $width
     * @param $height
     * @return false|string
     */
    public function resize($url, $width, $height = 0)
    {
        if ($height == 0) {
            $height = $width;
        }
        return $this->getSquareImage($url, $width, $height);
    }

    public function getSquareImage($imgUrl, $width, $height)
    {
        $imgPath = $this->_splitImageValue($imgUrl, 'path');
        $imgName = $this->_splitImageValue($imgUrl, 'name');

        // Path with Directory Separator
        $imgPath = str_replace('/', DIRECTORY_SEPARATOR, $imgPath);

        // Absolute full path of Image
        $mediaPath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('');
        $imgPathFull = $mediaPath . DIRECTORY_SEPARATOR . $imgPath . DIRECTORY_SEPARATOR . $imgName;

        // Resize folder is widthXheight
        $resizeFolder = 'cache' . DIRECTORY_SEPARATOR . $width . 'x' . $height;

        // Image resized path will then be
        $imageResizedPath = $mediaPath . DIRECTORY_SEPARATOR .
            $imgPath . DIRECTORY_SEPARATOR .
            $resizeFolder . DIRECTORY_SEPARATOR .
            $imgName;

        /**
         * First check in cache i.e image resized path
         * If not in cache then create image of the width=X and height = Y
         */
        if (!file_exists($imageResizedPath)) {
            if (file_exists($imgPathFull)) {
                $imageObj = $this->_imageFactory->create(['fileName' => $imgPathFull]);
                $imageObj->constrainOnly(true);
                $imageObj->keepAspectRatio(true);
                $imageObj->keepFrame(false);
                $imageObj->quality(100);

                $imageObj->resize($width, $height);
                $imageObj->save($imageResizedPath);

                unset($imageObj);

                if (!file_exists($imageResizedPath)) {
                    return false;
                }
            } else {
                return false;
            }
        }

        // Return full http path of the image

        return $this->_store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . "$imgPath/$resizeFolder/$imgName";
    }

    private function _splitImageValue($imageValue, $attr)
    {
        $imArray = explode('/', $imageValue);

        $name = $imArray[count($imArray)-1];
        if ($attr === 'path') {
            return implode('/', array_diff($imArray, [$name]));
        }

        return $name;
    }
}
