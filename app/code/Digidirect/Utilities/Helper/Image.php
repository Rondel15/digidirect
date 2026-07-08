<?php
namespace Digidirect\Utilities\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Image
 * @package Digidirect\Utilities\Helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Image extends AbstractHelper
{
    /**
     * Images quality var
     */
    const IMAGES_QUALITY_VAR = 'images_quality';

    /**
     * Images quality var
     */
    const DEFAULT_QUALITY_VAR = 'default';

    /**
     * Media config node
     */
    const MEDIA_TYPE_CONFIG_NODE = 'images';

    /**
     * Current model
     *
     * @var \Digidirect\Utilities\Model\Image
     */
    protected $model;

    /**
     * Scheduled for resize image
     *
     * @var bool
     */
    protected $scheduleResize = true;

    /**
     * Scheduled for rotate image
     *
     * @var bool
     */
    protected $scheduleRotate = false;

    /**
     * Angle
     *
     * @var int
     */
    protected $angle;

    /**
     * Watermark file name
     *
     * @var string
     */
    protected $watermark;

    /**
     * Watermark Position
     *
     * @var string
     */
    protected $watermarkPosition;

    /**
     * Watermark Size
     *
     * @var string
     */
    protected $watermarkSize;

    /**
     * Watermark Image opacity
     *
     * @var int
     */
    protected $watermarkImageOpacity;

    /**
     * Image File
     *
     * @var string
     */
    protected $imageFile;

    /**
     * Image Placeholder
     *
     * @var string
     */
    protected $placeholder;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * Product image factory
     *
     * @var \Digidirect\Utilities\Model\ImageFactory
     */
    protected $imageFactory;

    /**
     * @var \Magento\Framework\View\ConfigInterface
     */
    protected $viewConfig;

    /**
     * @var \Magento\Framework\Config\View
     */
    protected $configView;

    /**
     * Image configuration attributes
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $entity;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Digidirect\Utilities\Model\ImageFactory $imageFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\ConfigInterface $viewConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Digidirect\Utilities\Model\ImageFactory $imageFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\ConfigInterface $viewConfig
    ) {
        $this->imageFactory = $imageFactory;
        $this->assetRepo = $assetRepo;
        $this->viewConfig = $viewConfig;
        parent::__construct($context);
    }

    /**
     * Reset all previous data
     *
     * @return $this
     */
    protected function _reset()
    {
        $this->model = null;
        $this->scheduleRotate = false;
        $this->angle = null;
        $this->watermark = null;
        $this->watermarkPosition = null;
        $this->watermarkSize = null;
        $this->watermarkImageOpacity = null;
        $this->imageFile = null;
        $this->attributes = [];
        $this->entity = null;
        return $this;
    }

    /**
     * Initialize Helper to work with Image
     *
     * @param \Magento\Framework\DataObject $entity
     * @param string $imageId
     * @param array $attributes
     * @return $this
     */
    public function init($entity, $imageId, $attributes = [])
    {
        $this->_reset();

        $this->attributes = array_merge(
            $this->getConfigView()->getMediaAttributes('Magento_Catalog', self::MEDIA_TYPE_CONFIG_NODE, $imageId),
            $attributes
        );
        $this->setEntity($entity);
        $this->setImageProperties();
        $this->setWatermarkProperties();

        return $this;
    }

    /**
     * Set current Entity
     *
     * @param \Magento\Framework\DataObject $entity
     * @return $this
     */
    protected function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * Get current Entity
     *
     * @return \Magento\Framework\DataObject
     */
    protected function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set image properties
     *
     * @return $this
     */
    protected function setImageProperties()
    {
        $this->_getModel()->setDestinationSubdir($this->getType());

        $this->_getModel()->setWidth($this->getWidth());
        $this->_getModel()->setHeight($this->getHeight());

        // Set 'keep frame' flag
        $frame = $this->getFrame();
        if (!empty($frame)) {
            $this->_getModel()->setKeepFrame($frame);
        }

        // Set 'constrain only' flag
        $constrain = $this->getAttribute('constrain');
        if (!empty($constrain)) {
            $this->_getModel()->setConstrainOnly($constrain);
        }

        // Set 'keep aspect ratio' flag
        $aspectRatio = $this->getAttribute('aspect_ratio');
        if (!empty($aspectRatio)) {
            $this->_getModel()->setKeepAspectRatio($aspectRatio);
        }

        // Set 'transparency' flag
        $transparency = $this->getAttribute('transparency');
        if (!empty($transparency)) {
            $this->_getModel()->setKeepTransparency($transparency);
        }

        // Set background color
        $background = $this->getAttribute('background');
        if (!empty($background)) {
            $this->_getModel()->setBackgroundColor($background);
        }

        return $this;
    }

    /**
     * Set watermark properties
     *
     * @return $this
     */
    protected function setWatermarkProperties()
    {
        $this->setWatermark(
            $this->scopeConfig->getValue(
                "design/watermark/{$this->_getModel()->getDestinationSubdir()}_image",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
        $this->setWatermarkImageOpacity(
            $this->scopeConfig->getValue(
                "design/watermark/{$this->_getModel()->getDestinationSubdir()}_imageOpacity",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
        $this->setWatermarkPosition(
            $this->scopeConfig->getValue(
                "design/watermark/{$this->_getModel()->getDestinationSubdir()}_position",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
        $this->setWatermarkSize(
            $this->scopeConfig->getValue(
                "design/watermark/{$this->_getModel()->getDestinationSubdir()}_size",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
        return $this;
    }

    /**
     * Schedule resize of the image
     * $width *or* $height can be null - in this case, lacking dimension will be calculated.
     *
     * @param int $width
     * @param int $height
     * @return $this
     */
    public function resize($width, $height = null)
    {
        $this->_getModel()->setWidth($width)->setHeight($height);
        $this->scheduleResize = true;
        return $this;
    }

    /**
     * Set image quality, values in percentage from 0 to 100
     *
     * @param int $quality
     * @return $this
     */
    public function setQuality($quality)
    {
        $this->_getModel()->setQuality($quality);
        return $this;
    }

    /**
     * Guarantee, that image picture width/height will not be distorted.
     * Applicable before calling resize()
     * It is true by default.
     *
     * @param bool $flag
     * @return $this
     */
    public function keepAspectRatio($flag)
    {
        $this->_getModel()->setKeepAspectRatio($flag);
        return $this;
    }

    /**
     * Guarantee, that image will have dimensions, set in $width/$height
     * Applicable before calling resize()
     * Not applicable, if keepAspectRatio(false)
     *
     * $position - TODO, not used for now - picture position inside the frame.
     *
     * @param bool $flag
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function keepFrame($flag)
    {
        $this->_getModel()->setKeepFrame($flag);
        return $this;
    }

    /**
     * Guarantee, that image will not lose transparency if any.
     * Applicable before calling resize()
     * It is true by default.
     *
     * $alphaOpacity - TODO, not used for now
     *
     * @param bool $flag
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function keepTransparency($flag)
    {
        $this->_getModel()->setKeepTransparency($flag);
        return $this;
    }

    /**
     * Guarantee, that image picture will not be bigger, than it was.
     * Applicable before calling resize()
     * It is false by default
     *
     * @param bool $flag
     * @return $this
     */
    public function constrainOnly($flag)
    {
        $this->_getModel()->setConstrainOnly($flag);
        return $this;
    }

    /**
     * Set color to fill image frame with.
     * Applicable before calling resize()
     * The keepTransparency(true) overrides this (if image has transparent color)
     * It is white by default.
     *
     * @param array $colorRGB
     * @return $this
     */
    public function backgroundColor($colorRGB)
    {
        // assume that 3 params were given instead of array
        if (!is_array($colorRGB)) {
            $colorRGB = func_get_args();
        }
        $this->_getModel()->setBackgroundColor($colorRGB);
        return $this;
    }

    /**
     * Rotate image into specified angle
     *
     * @param int $angle
     * @return $this
     */
    public function rotate($angle)
    {
        $this->setAngle($angle);
        $this->_getModel()->setAngle($angle);
        $this->scheduleRotate = true;
        return $this;
    }

    /**
     * Add watermark to image
     * size param in format 100x200
     *
     * @param string $fileName
     * @param string $position
     * @param string $size
     * @param int $imageOpacity
     * @return $this
     */
    public function watermark($fileName, $position, $size = null, $imageOpacity = null)
    {
        $this->setWatermark(
            $fileName
        )->setWatermarkPosition(
            $position
        )->setWatermarkSize(
            $size
        )->setWatermarkImageOpacity(
            $imageOpacity
        );
        return $this;
    }

    /**
     * Set placeholder
     *
     * @param string $fileName
     * @return void
     */
    public function placeholder($fileName)
    {
        $this->placeholder = $fileName;
    }

    /**
     * Get Placeholder
     *
     * @param null|string $placeholder
     * @return string
     */
    public function getPlaceholder($placeholder = null)
    {
        if ($placeholder) {
            $placeholderFullPath = 'Magento_Catalog::images/product/placeholder/' . $placeholder . '.jpg';
        } else {
            $placeholderFullPath = $this->placeholder
                ?: 'Magento_Catalog::images/product/placeholder/' . $this->_getModel()->getDestinationSubdir() . '.jpg';
        }
        return $placeholderFullPath;
    }

    /**
     * Apply scheduled actions
     *
     * @return $this
     * @throws \Exception
     */
    protected function applyScheduledActions()
    {
        $this->initBaseFile();
        if ($this->isScheduledActionsAllowed()) {
            $model = $this->_getModel();
            if ($this->scheduleRotate) {
                $model->rotate($this->getAngle());
            }
            if ($this->scheduleResize) {
                $model->resize();
            }
            if ($this->getWatermark()) {
                $model->setWatermark($this->getWatermark());
            }
            $model->saveFile();
        }
        return $this;
    }

    /**
     * Initialize base image file
     *
     * @return $this
     * @throws \Exception
     */
    protected function initBaseFile()
    {
        $model = $this->_getModel();
        $baseFile = $model->getBaseFile();
        if (!$baseFile) {
            if ($this->getImageFile()) {
                $model->setBaseFile($this->getImageFile());
            } else {
                $model->setBaseFile($this->getEntity()->getData($model->getDestinationSubdir()));
            }
        }
        return $this;
    }

    /**
     * Check if scheduled actions is allowed
     *
     * @return bool
     */
    protected function isScheduledActionsAllowed()
    {
        $model = $this->_getModel();
        if ($model->isBaseFilePlaceholder()
            && $model->getNewFile() === true
            || $model->isCached()
        ) {
            return false;
        }
        return true;
    }

    /**
     * Retrieve image URL
     *
     * @return string
     */
    public function getUrl()
    {
        try {
            $this->applyScheduledActions();
            return $this->_getModel()->getUrl();
        } catch (\Exception $e) {
            return $this->getDefaultPlaceholderUrl();
        }
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function save()
    {
        $this->applyScheduledActions();
        return $this;
    }

    /**
     * Return resized product image information
     *
     * @return array
     * @throws \Exception
     */
    public function getResizedImageInfo()
    {
        $this->applyScheduledActions();
        return $this->_getModel()->getResizedImageInfo();
    }

    /**
     * @param null|string $placeholder
     * @return string
     */
    public function getDefaultPlaceholderUrl($placeholder = null)
    {
        try {
            $url = $this->assetRepo->getUrl($this->getPlaceholder($placeholder));
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            $url = $this->_urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']);
        }
        return $url;
    }

    /**
     * Get current Image model
     *
     * @return \Digidirect\Utilities\Model\Image
     */
    protected function _getModel()
    {
        if (!$this->model) {
            $this->model = $this->imageFactory->create();
        }
        return $this->model;
    }

    /**
     * Set Rotation Angle
     *
     * @param int $angle
     * @return $this
     */
    protected function setAngle($angle)
    {
        $this->angle = $angle;
        return $this;
    }

    /**
     * Get Rotation Angle
     *
     * @return int
     */
    protected function getAngle()
    {
        return $this->angle;
    }

    /**
     * Set watermark file name
     *
     * @param string $watermark
     * @return $this
     */
    protected function setWatermark($watermark)
    {
        $this->watermark = $watermark;
        $this->_getModel()->setWatermarkFile($watermark);
        return $this;
    }

    /**
     * Get watermark file name
     *
     * @return string
     */
    protected function getWatermark()
    {
        return $this->watermark;
    }

    /**
     * Set watermark position
     *
     * @param string $position
     * @return $this
     */
    protected function setWatermarkPosition($position)
    {
        $this->watermarkPosition = $position;
        $this->_getModel()->setWatermarkPosition($position);
        return $this;
    }

    /**
     * Get watermark position
     *
     * @return string
     */
    protected function getWatermarkPosition()
    {
        return $this->watermarkPosition;
    }

    /**
     * Set watermark size
     * param size in format 100x200
     *
     * @param string $size
     * @return $this
     */
    public function setWatermarkSize($size)
    {
        $this->watermarkSize = $size;
        $this->_getModel()->setWatermarkSize($this->parseSize($size));
        return $this;
    }

    /**
     * Get watermark size
     *
     * @return string
     */
    protected function getWatermarkSize()
    {
        return $this->watermarkSize;
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
        $this->_getModel()->setWatermarkImageOpacity($imageOpacity);
        return $this;
    }

    /**
     * Get watermark image opacity
     *
     * @return int
     */
    protected function getWatermarkImageOpacity()
    {
        if ($this->watermarkImageOpacity) {
            return $this->watermarkImageOpacity;
        }

        return $this->_getModel()->getWatermarkImageOpacity();
    }

    /**
     * Set Image file
     *
     * @param string $file
     * @return $this
     */
    public function setImageFile($file)
    {
        $this->imageFile = $file;
        return $this;
    }

    /**
     * Get Image file
     *
     * @return string
     */
    protected function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * Retrieve size from string
     *
     * @param string $string
     * @return array|bool
     */
    protected function parseSize($string)
    {
        if (!is_null($string)) {
            $size = explode('x', strtolower($string));
            if (sizeof($size) == 2) {
                return ['width' => $size[0] > 0 ? $size[0] : null, 'height' => $size[1] > 0 ? $size[1] : null];
            }
        }
        return false;
    }

    /**
     * Retrieve original image width
     *
     * @return int|null
     */
    public function getOriginalWidth()
    {
        return $this->_getModel()->getImageProcessor()->getOriginalWidth();
    }

    /**
     * Retrieve original image height
     *
     * @return int|null
     */
    public function getOriginalHeight()
    {
        return $this->_getModel()->getImageProcessor()->getOriginalHeight();
    }

    /**
     * Retrieve Original image size as array
     * 0 - width, 1 - height
     *
     * @return int[]
     */
    public function getOriginalSizeArray()
    {
        return [$this->getOriginalWidth(), $this->getOriginalHeight()];
    }

    /**
     * Retrieve config view
     *
     * @return \Magento\Framework\Config\View
     */
    protected function getConfigView()
    {
        if (!$this->configView) {
            $this->configView = $this->viewConfig->getViewConfig();
        }
        return $this->configView;
    }

    /**
     * Retrieve image type
     *
     * @return string
     */
    public function getType()
    {
        return $this->getAttribute('type');
    }

    /**
     * Retrieve image width
     *
     * @return string
     */
    public function getWidth()
    {
        return $this->getAttribute('width');
    }

    /**
     * Retrieve image height
     *
     * @return string
     */
    public function getHeight()
    {
        return $this->getAttribute('height') ?: $this->getAttribute('width');
    }

    /**
     * Retrieve image frame flag
     *
     * @return false|string
     */
    public function getFrame()
    {
        $frame = $this->getAttribute('frame');
        if (empty($frame)) {
            $frame = $this->getConfigView()->getVarValue('Magento_Catalog', 'product_image_white_borders');
        }
        return $frame;
    }

    /**
     * Retrieve image attribute
     *
     * @param string $name
     * @return string
     */
    protected function getAttribute($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    /**
     * Get image quality by image type ID
     *
     * @param string $imageTypeId
     * @return int|bool
     */
    public function getImageQualityByType($imageTypeId)
    {
        $config = $this->viewConfig->getViewConfig([
            'area' => Area::AREA_FRONTEND
        ]);

        $imagesQuality = $config->getVarValue('Magento_Catalog', self::IMAGES_QUALITY_VAR);
        if (isset($imagesQuality[$imageTypeId])) {
            return $imagesQuality[$imageTypeId];
        }
        if (isset($imagesQuality[self::DEFAULT_QUALITY_VAR])) {
            return $imagesQuality[self::DEFAULT_QUALITY_VAR];
        }

        return false;
    }

    /**
     * Return image label
     *
     * @return string
     */
    public function getLabel()
    {
        $label = $this->entity->getData($this->getType() . '_' . 'label');
        if (empty($label)) {
            $label = $this->entity->getName();
        }
        return $label;
    }
}
