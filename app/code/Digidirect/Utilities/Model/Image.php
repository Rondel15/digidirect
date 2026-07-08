<?php

namespace Digidirect\Utilities\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Image as MagentoImage;

class Image extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var int
     */
    protected $width;

    /**
     * @var int
     */
    protected $height;

    /**
     * Default quality value (for JPEG images only).
     *
     * @var int
     */
    protected $quality = 80;

    /**
     * @var bool
     */
    protected $keepAspectRatio = true;

    /**
     * @var bool
     */
    protected $keepFrame = true;

    /**
     * @var bool
     */
    protected $keepTransparency = true;

    /**
     * @var bool
     */
    protected $constrainOnly = true;

    /**
     * @var int[]
     */
    protected $backgroundColor = [255, 255, 255];

    /**
     * @var string
     */
    protected $baseFile;

    /**
     * @var bool
     */
    protected $isBaseFilePlaceholder;

    /**
     * @var string|bool
     */
    protected $newFile;

    /**
     * @var MagentoImage
     */
    protected $processor;

    /**
     * @var string
     */
    protected $destinationSubdir;

    /**
     * @var int
     */
    protected $angle;

    /**
     * @var string
     */
    protected $watermarkFile;

    /**
     * @var int
     */
    protected $watermarkPosition;

    /**
     * @var int
     */
    protected $watermarkWidth;

    /**
     * @var int
     */
    protected $watermarkHeight;

    /**
     * @var int
     */
    protected $watermarkImageOpacity = 70;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Framework\Image\Factory
     */
    protected $imageFactory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var \Magento\Framework\View\FileSystem
     */
    protected $viewFileSystem;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $coreFileStorageDatabase = null;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Digidirect\Utilities\Model\Media\ConfigInterface
     */
    protected $mediaConfig;

    /**
     * @var \Magento\Framework\View\Asset\LocalInterface
     */
    private $imageAsset;

    /**
     * @var \Magento\Catalog\Model\View\Asset\ImageFactory
     */
    private $viewAssetImageFactory;

    /**
     * Image constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase
     * @param \Magento\Framework\Filesystem $filesystem
     * @param MagentoImage\Factory $imageFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\FileSystem $viewFileSystem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Media\ConfigInterface $mediaConfig
     * @param \Magento\Catalog\Model\View\Asset\ImageFactory|null $viewAssetImageFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\Factory $imageFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\FileSystem $viewFileSystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Digidirect\Utilities\Model\Media\ConfigInterface $mediaConfig,
        \Magento\Catalog\Model\View\Asset\ImageFactory $viewAssetImageFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->imageFactory = $imageFactory;
        $this->assetRepo = $assetRepo;
        $this->viewFileSystem = $viewFileSystem;
        $this->scopeConfig = $scopeConfig;
        $this->mediaConfig = $mediaConfig;
        $this->viewAssetImageFactory = $viewAssetImageFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @param int $width
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $height
     * @return $this
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set image quality, values in percentage from 0 to 100
     *
     * @param int $quality
     * @return $this
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;
        return $this;
    }

    /**
     * Get image quality
     *
     * @return int
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * @param bool $keep
     * @return $this
     */
    public function setKeepAspectRatio($keep)
    {
        $this->keepAspectRatio = $keep && $keep !== 'false';
        return $this;
    }

    /**
     * @param bool $keep
     * @return $this
     */
    public function setKeepFrame($keep)
    {
        $this->keepFrame = $keep && $keep !== 'false';
        return $this;
    }

    /**
     * @param bool $keep
     * @return $this
     */
    public function setKeepTransparency($keep)
    {
        $this->keepTransparency = $keep && $keep !== 'false';
        return $this;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setConstrainOnly($flag)
    {
        $this->constrainOnly = $flag && $flag !== 'false';
        return $this;
    }

    /**
     * @param int[] $rgbArray
     * @return $this
     */
    public function setBackgroundColor(array $rgbArray)
    {
        $this->backgroundColor = $rgbArray;
        return $this;
    }

    /**
     * @param string $size
     * @return $this
     */
    public function setSize($size)
    {
        // determine width and height from string
        list($width, $height) = explode('x', strtolower($size), 2);
        foreach (['width', 'height'] as $wh) {
            ${$wh}
                = (int)${$wh};
            if (empty(${$wh})) {
                ${$wh}
                    = null;
            }
        }

        // set sizes
        $this->setWidth($width)->setHeight($height);

        return $this;
    }

    /**
     * @param string|null $file
     * @return bool
     */
    protected function _checkMemory($file = null)
    {
        return $this->_getMemoryLimit() > $this->_getMemoryUsage() + $this->_getNeedMemoryForFile(
            $file
        )
        || $this->_getMemoryLimit() == -1;
    }

    /**
     * @return string
     */
    protected function _getMemoryLimit()
    {
        $memoryLimit = trim(strtoupper(ini_get('memory_limit')));

        if (!isset($memoryLimit[0])) {
            $memoryLimit = "128M";
        }

        if (substr($memoryLimit, -1) == 'K') {
            return substr($memoryLimit, 0, -1) * 1024;
        }
        if (substr($memoryLimit, -1) == 'M') {
            return substr($memoryLimit, 0, -1) * 1024 * 1024;
        }
        if (substr($memoryLimit, -1) == 'G') {
            return substr($memoryLimit, 0, -1) * 1024 * 1024 * 1024;
        }
        return $memoryLimit;
    }

    /**
     * @return int
     */
    protected function _getMemoryUsage()
    {
        if (function_exists('memory_get_usage')) {
            return memory_get_usage();
        }
        return 0;
    }

    /**
     * @param string|null $file
     * @return float|int
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getNeedMemoryForFile($file = null)
    {
        $file = $file === null ? $this->getBaseFile() : $file;
        if (!$file) {
            return 0;
        }

        if (!$this->mediaDirectory->isExist($file)) {
            return 0;
        }

        $imageInfo = getimagesize($this->mediaDirectory->getAbsolutePath($file));

        if (!isset($imageInfo[0]) || !isset($imageInfo[1])) {
            return 0;
        }
        if (!isset($imageInfo['channels'])) {
            // if there is no info about this parameter lets set it for maximum
            $imageInfo['channels'] = 4;
        }
        if (!isset($imageInfo['bits'])) {
            // if there is no info about this parameter lets set it for maximum
            $imageInfo['bits'] = 8;
        }
        return round(
            ($imageInfo[0] * $imageInfo[1] * $imageInfo['bits'] * $imageInfo['channels'] / 8 + Pow(2, 16)) * 1.65
        );
    }

    /**
     * Convert array of 3 items (decimal r, g, b) to string of their hex values
     *
     * @param int[] $rgbArray
     * @return string
     */
    protected function _rgbToString($rgbArray)
    {
        $result = [];
        foreach ($rgbArray as $value) {
            if (null === $value) {
                $result[] = 'null';
            } else {
                $result[] = sprintf('%02s', dechex($value));
            }
        }
        return implode($result);
    }

    /**
     * Set filenames for base file and new file
     *
     * @param string $file
     * @return $this
     * @throws \Exception
     */
    public function setBaseFile($file)
    {
        $this->isBaseFilePlaceholder = false;

        $this->imageAsset = $this->viewAssetImageFactory->create(
            [
                'miscParams' => $this->getMiscParams(),
                'filePath' => $file,
            ]
        );

        if ($file == 'no_selection' || !$this->_fileExists($this->imageAsset->getSourceFile())
            || !$this->_checkMemory($this->imageAsset->getSourceFile())
        ) {
            $this->isBaseFilePlaceholder = true;
        }

        $this->baseFile = $this->imageAsset->getSourceFile();

        return $this;
    }

    /**
     * @return string
     */
    public function getBaseFile()
    {
        return $this->baseFile;
    }

    /**
     * @deprecated 101.1.0
     * @return bool|string
     */
    public function getNewFile()
    {
        return $this->newFile;
    }

    /**
     * Retrieve 'true' if image is a base file placeholder
     *
     * @return bool
     */
    public function isBaseFilePlaceholder()
    {
        return (bool)$this->isBaseFilePlaceholder;
    }

    /**
     * @param MagentoImage $processor
     * @return $this
     */
    public function setImageProcessor($processor)
    {
        $this->processor = $processor;
        return $this;
    }

    /**
     * @return MagentoImage
     */
    public function getImageProcessor()
    {
        if (!$this->processor) {
            $filename = $this->getBaseFile() ? $this->mediaDirectory->getAbsolutePath($this->getBaseFile()) : null;
            $this->processor = $this->imageFactory->create($filename);
        }
        $this->processor->keepAspectRatio($this->keepAspectRatio);
        $this->processor->keepFrame($this->keepFrame);
        $this->processor->keepTransparency($this->keepTransparency);
        $this->processor->constrainOnly($this->constrainOnly);
        $this->processor->backgroundColor($this->backgroundColor);
        $this->processor->quality($this->quality);
        return $this->processor;
    }

    /**
     * @see \Magento\Framework\Image\Adapter\AbstractAdapter
     * @return $this
     */
    public function resize()
    {
        if ($this->getWidth() === null && $this->getHeight() === null) {
            return $this;
        }
        $this->getImageProcessor()->resize($this->width, $this->height);
        return $this;
    }

    /**
     * @param int $angle
     * @return $this
     */
    public function rotate($angle)
    {
        $angle = intval($angle);
        $this->getImageProcessor()->rotate($angle);
        return $this;
    }

    /**
     * Set angle for rotating
     *
     * This func actually affects only the cache filename.
     *
     * @param int $angle
     * @return $this
     */
    public function setAngle($angle)
    {
        $this->angle = $angle;
        return $this;
    }

    /**
     * Add watermark to image
     * size param in format 100x200
     *
     * @param string $file
     * @param string|null $position
     * @param array $size ['width' => int, 'height' => int]
     * @param int $width
     * @param int $height
     * @param int $opacity
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function setWatermark(
        $file,
        $position = null,
        $size = null,
        $width = null,
        $height = null,
        $opacity = null
    ) {
        if ($this->isBaseFilePlaceholder) {
            return $this;
        }

        if ($file) {
            $this->setWatermarkFile($file);
        } else {
            return $this;
        }

        if ($position) {
            $this->setWatermarkPosition($position);
        }
        if ($size) {
            $this->setWatermarkSize($size);
        }
        if ($width) {
            $this->setWatermarkWidth($width);
        }
        if ($height) {
            $this->setWatermarkHeight($height);
        }
        if ($opacity) {
            $this->setWatermarkImageOpacity($opacity);
        }
        $filePath = $this->_getWatermarkFilePath();

        if ($filePath) {
            $imagePreprocessor = $this->getImageProcessor();
            $imagePreprocessor->setWatermarkPosition($this->getWatermarkPosition());
            $imagePreprocessor->setWatermarkImageOpacity($this->getWatermarkImageOpacity());
            $imagePreprocessor->setWatermarkWidth($this->getWatermarkWidth());
            $imagePreprocessor->setWatermarkHeight($this->getWatermarkHeight());
            $imagePreprocessor->watermark($filePath);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function saveFile()
    {
        if ($this->isBaseFilePlaceholder) {
            return $this;
        }
        $filename = $this->getBaseFile() ? $this->imageAsset->getPath() : null;
        $this->getImageProcessor()->save($filename);
        $this->coreFileStorageDatabase->saveFile($filename);
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->imageAsset->getUrl();
    }

    /**
     * @param string $dir
     * @return $this
     */
    public function setDestinationSubdir($dir)
    {
        $this->destinationSubdir = $dir;
        return $this;
    }

    /**
     * @return string
     */
    public function getDestinationSubdir()
    {
        return $this->destinationSubdir;
    }

    /**
     * @return bool
     */
    public function isCached()
    {
        return file_exists($this->imageAsset->getPath());
    }

    /**
     * Set watermark file name
     *
     * @param string $file
     * @return $this
     */
    public function setWatermarkFile($file)
    {
        $this->watermarkFile = $file;
        return $this;
    }

    /**
     * Get watermark file name
     *
     * @return string
     */
    public function getWatermarkFile()
    {
        return $this->watermarkFile;
    }

    /**
     * Get relative watermark file path
     * or false if file not found
     *
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getWatermarkFilePath()
    {
        $filePath = false;

        if (!($file = $this->getWatermarkFile())) {
            return $filePath;
        }
        $baseDir = $this->mediaConfig->getBaseMediaPath();

        $candidates = [
            $baseDir . '/watermark/stores/' . $this->storeManager->getStore()->getId() . $file,
            $baseDir . '/watermark/websites/' . $this->storeManager->getWebsite()->getId() . $file,
            $baseDir . '/watermark/default/' . $file,
            $baseDir . '/watermark/' . $file,
        ];
        foreach ($candidates as $candidate) {
            if ($this->mediaDirectory->isExist($candidate)) {
                $filePath = $this->mediaDirectory->getAbsolutePath($candidate);
                break;
            }
        }
        if (!$filePath) {
            $filePath = $this->viewFileSystem->getStaticFileName($file);
        }

        return $filePath;
    }

    /**
     * Set watermark position
     *
     * @param string $position
     * @return $this
     */
    public function setWatermarkPosition($position)
    {
        $this->watermarkPosition = $position;
        return $this;
    }

    /**
     * Get watermark position
     *
     * @return string
     */
    public function getWatermarkPosition()
    {
        return $this->watermarkPosition;
    }

    /**
     * Set watermark image opacity
     *
     * @param int $imageOpacity
     * @return $this
     */
    public function setWatermarkImageOpacity($imageOpacity)
    {
        $this->watermarkImageOpacity = $imageOpacity;
        return $this;
    }

    /**
     * Get watermark image opacity
     *
     * @return int
     */
    public function getWatermarkImageOpacity()
    {
        return $this->watermarkImageOpacity;
    }

    /**
     * Set watermark size
     *
     * @param array $size
     * @return $this
     */
    public function setWatermarkSize($size)
    {
        if (is_array($size)) {
            $this->setWatermarkWidth($size['width'])->setWatermarkHeight($size['height']);
        }
        return $this;
    }

    /**
     * Set watermark width
     *
     * @param int $width
     * @return $this
     */
    public function setWatermarkWidth($width)
    {
        $this->watermarkWidth = $width;
        return $this;
    }

    /**
     * Get watermark width
     *
     * @return int
     */
    public function getWatermarkWidth()
    {
        return $this->watermarkWidth;
    }

    /**
     * Set watermark height
     *
     * @param int $height
     * @return $this
     */
    public function setWatermarkHeight($height)
    {
        $this->watermarkHeight = $height;
        return $this;
    }

    /**
     * Get watermark height
     *
     * @return string
     */
    public function getWatermarkHeight()
    {
        return $this->watermarkHeight;
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     * @return void
     */
    public function clearCache()
    {
        $directory = $this->mediaConfig->getBaseMediaPath() . '/cache';
        $this->mediaDirectory->delete($directory);

        $this->coreFileStorageDatabase->deleteFolder($this->mediaDirectory->getAbsolutePath($directory));
    }

    /**
     * First check this file on FS
     * If it doesn't exist - try to download it from DB
     *
     * @param string $filename
     * @return bool
     */
    protected function _fileExists($filename)
    {
        if ($this->mediaDirectory->isFile($filename)) {
            return true;
        } else {
            return $this->coreFileStorageDatabase->saveFileToFilesystem(
                $this->mediaDirectory->getAbsolutePath($filename)
            );
        }
    }

    /**
     * Return resized product image information
     *
     * @return array
     */
    public function getResizedImageInfo()
    {
        if ($this->isBaseFilePlaceholder() == true) {
            $image = $this->imageAsset->getSourceFile();
        } else {
            $image = $this->imageAsset->getPath();
        }
        return getimagesize($image);
    }

    /**
     * Retrieve misc params based on all image attributes
     *
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function getMiscParams()
    {
        $miscParams = [
            'image_type' => $this->getDestinationSubdir(),
            'image_height' => $this->getHeight(),
            'image_width' => $this->getWidth(),
            'keep_aspect_ratio' => ($this->keepAspectRatio ? '' : 'non') . 'proportional',
            'keep_frame' => ($this->keepFrame ? '' : 'no') . 'frame',
            'keep_transparency' => ($this->keepTransparency ? '' : 'no') . 'transparency',
            'constrain_only' => ($this->constrainOnly ? 'do' : 'not') . 'constrainonly',
            'angle' => $this->angle,
            'quality' => $this->quality,
        ];

        if (class_exists('\Magento\Catalog\Model\Product\Image\ParamsBuilder')) {
            $miscParams['background'] = $this->backgroundColor;
        } else {
            $miscParams['background'] = $this->_rgbToString($this->backgroundColor);
        }

        // if has watermark add watermark params to hash
        if ($this->getWatermarkFile()) {
            $miscParams['watermark_file'] = $this->getWatermarkFile();
            $miscParams['watermark_image_opacity'] = $this->getWatermarkImageOpacity();
            $miscParams['watermark_position'] = $this->getWatermarkPosition();
            $miscParams['watermark_width'] = $this->getWatermarkWidth();
            $miscParams['watermark_height'] = $this->getWatermarkHeight();
        }

        return $miscParams;
    }
}
