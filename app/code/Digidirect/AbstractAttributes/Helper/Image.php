<?php
namespace Digidirect\AbstractAttributes\Helper;

use Digidirect\AbstractAttributes\Api\Data\OptionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;

/**
 * Class Image
 * @package Digidirect\AbstractAttributes\Helper
 */
class Image extends \Magento\Catalog\Helper\Image
{
    const OPTION_IMAGE_ID = 'image';
    const OPTION_IMAGE_INFO = 'image_info';

    const OPTION_WIDGET_IMAGE_ID = 'widget_logo';
    const OPTION_WIDGET_LOGO_INFO = 'widget_logo_info';

    const IMAGES_INFO = [
        self::OPTION_IMAGE_ID        => self::OPTION_IMAGE_INFO,
        self::OPTION_WIDGET_IMAGE_ID => self::OPTION_WIDGET_LOGO_INFO
    ];

    /**
     * @var \Digidirect\AbstractAttributes\Model\Media\Config
     */
    protected $mediaConfig;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Framework\File\Mime
     */
    protected $mime;

    /**
     * Image constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\Product\ImageFactory $productImageFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\ConfigInterface $viewConfig
     * @param \Digidirect\AbstractAttributes\Model\Media\Config $mediaConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\File\Mime $mime
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\Product\ImageFactory $productImageFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\ConfigInterface $viewConfig,
        \Digidirect\AbstractAttributes\Model\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Mime $mime
    ) {
        $this->mediaConfig = $mediaConfig;
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mime = $mime;

        parent::__construct($context, $productImageFactory, $assetRepo, $viewConfig);
    }

    /**
     * Initialize Helper to work with Image
     * @param OptionInterface|\Magento\Catalog\Model\Product $option
     * @param string $imageId
     * @param array $attributes
     * @return $this
     */
    public function init($option, $imageId, $attributes = [])
    {
        parent::init($option, 'eaa_attribute_option_listing', $attributes);
        $this->setImageFile($this->getOptionImage($option, $imageId));
        return $this;
    }

    /**
     * Get image url from option object
     * @param DataObject $option
     * @param string $key
     * @return string
     */
    public function getOriginalImageUrl($option = null, $key = self::OPTION_IMAGE_ID)
    {
        return $this->mediaConfig->getBaseMediaUrl() . $this->getOptionImage($option, $key);
    }

    /**
     * Get image from option object
     * @param DataObject|null $option
     * @param string $key
     * @return string
     */
    public function getOptionImage(DataObject $option = null, $key = self::OPTION_IMAGE_ID)
    {
        if (null === $option) {
            $option = $this->getProduct();
        }
        return $option->getData($key);
    }

    /**
     * Get image information from option
     * @param DataObject $option
     * @return DataObject
     */
    public function addOptionImagesInfo(DataObject $option)
    {
        foreach (array_keys(self::IMAGES_INFO) as $key) {
            $this->addImageInfo($option, $key);
        }

        return $option;
    }

    /**
     * Get page logo image information from option
     * @param DataObject|array $option
     * @param bool $delete
     * @return \string[]
     */
    public function getOptionImageInfo($option, $delete = true)
    {
        return $this->getImageInfo($option, self::OPTION_IMAGE_INFO, $delete);
    }

    /**
     * Get widget logo image information from option
     * @param DataObject|array $option
     * @param bool $delete
     * @return \string[]
     */
    public function getOptionWidgetImageInfo($option, $delete = true)
    {
        return $this->getImageInfo($option, self::OPTION_WIDGET_LOGO_INFO, $delete);
    }

    /**
     * Get image information from option
     * @param DataObject|array $option
     * @param string $key
     * @param bool $delete
     * @return \string[]
     */
    public function getImageInfo($option, $key = self::OPTION_IMAGE_INFO, $delete = true)
    {
        $imageInfo = [];
        if ($option instanceof DataObject) {
            $imageInfo = $option->getData($key);
            if ($delete) {
                $option->unsetData($key);
            }
        } elseif (is_array($option) && isset($option[$key])) {
            $imageInfo = $option[$key];
            if ($delete) {
                unset($option[$key]);
            }
        }

        if (is_array($imageInfo) && !empty($imageInfo)) {
            return current($imageInfo);
        }

        return [];
    }

    /**
     * Get image information from option
     * @param DataObject|array|string $option
     * @param string $key
     * @return bool
     */
    public function deleteOldImage($option, $key = self::OPTION_IMAGE_ID)
    {
        if ($option instanceof DataObject) {
            $images = $option->getData($key);
        } elseif (is_array($option)) {
            $images = !empty($option[$key]) ? $option[$key] : $option;
        } else {
            $images = strval($option);
        }

        if (!empty($images)) {
            if (!is_array($images)) {
                $images = [$images];
            }

            foreach ($images as $image) {
                if (!$image) {
                    continue;
                }
                $fileName = $this->mediaConfig->getBaseMediaPath() . $image;
                $this->mediaDirectory->delete($fileName);
            }

            return true;
        }

        return false;
    }

    /**
     * Get image information from option
     * @param DataObject $option
     * @param string $key
     * @return DataObject
     */
    protected function addImageInfo(DataObject $option, $key = self::OPTION_IMAGE_ID)
    {
        if ($image = $option->getData($key)) {
            $fileName = $this->mediaConfig->getBaseMediaPath() . $image;
        }

        $infoKey = self::IMAGES_INFO[$key];
        if (isset($fileName) && $this->mediaDirectory->isExist($fileName)) {
            $stat = $this->mediaDirectory->stat($fileName);
            $imageInfo = [
                [
                    'url'    => $this->getOriginalImageUrl($option, $key),
                    'file'   => $image,
                    'size'   => is_array($stat) ? $stat['size'] : 0,
                    'exists' => true,
                    'type' => $this->mime->getMimeType($this->mediaDirectory->getAbsolutePath($fileName)),
                ]
            ];
            $option->setData($infoKey, $imageInfo);
        } else {
            $option->unsetData($infoKey);
        }

        return $option;
    }
}
