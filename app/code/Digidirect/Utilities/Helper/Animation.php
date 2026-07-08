<?php
namespace Digidirect\Utilities\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\Json\Helper\Data as JsonHelper;

class Animation extends AbstractHelper
{
    const LOADING_ANIMATION_ENABLED = 'enable';
    const LOADING_ANIMATION_IMAGE = 'image';
    const LOADING_ANIMATION_TEXT = 'text';
    const LOADING_ANIMATION_UPLOAD_DIR = 'animation/image';

    /**
     * Json Helper Object
     * @var JsonHelper
     */
    protected $_jsonHelper = null;

    /**
     * Asset service
     *
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * Animation constructor.
     * @param Context $context
     * @param JsonHelper $jsonHelper
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        Context $context,
        JsonHelper $jsonHelper,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->_jsonHelper = $jsonHelper;
        $this->_assetRepo = $assetRepo;
        $this->_escaper = $escaper;
        parent::__construct($context);
    }

    /**
     * Get value from stores/configuration/design/loading animation
     * @param string $param
     * @return string
     */
    protected function _getLoadingAnimationSetting($param)
    {
        return $this->scopeConfig->getValue(
            'design/loading_animation/' . $param,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if custom animation is enabled
     * @return string
     */
    public function isEnabled()
    {
        return $this->_getLoadingAnimationSetting(self::LOADING_ANIMATION_ENABLED);
    }

    /**
     * Get image
     * @return string
     */
    public function getImage()
    {
        if ($this->isEnabled()) {
            $folderName = self::LOADING_ANIMATION_UPLOAD_DIR;
            $path = $folderName . '/' . $this->_getLoadingAnimationSetting(self::LOADING_ANIMATION_IMAGE);
            $logoUrl = $this->_urlBuilder
                    ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;
        } else {
            $logoUrl = $this->_assetRepo->getUrlWithParams('images/loader-2.gif', []);
        }
        return $logoUrl;
    }

    /**
     * Get loading animation text
     * @return string
     */
    public function getText()
    {
        if ($this->isEnabled()) {
            $text = $this->_getLoadingAnimationSetting(self::LOADING_ANIMATION_TEXT);
            return $this->_escaper->escapeQuote(__($text));
        }

        return '';
    }

    /**
     * Get whole custom animation config as json or as array
     * @param bool $asJson
     * @return string | []
     */
    public function getLoadingAnimationConfig($asJson = false)
    {
        $array = [
            self::LOADING_ANIMATION_IMAGE => $this->getImage(),
            self::LOADING_ANIMATION_TEXT => $this->getText(),
            self::LOADING_ANIMATION_ENABLED => $this->isEnabled()
        ];

        if ($asJson) {
            return $this->_jsonHelper->jsonEncode($array);
        }
        return $array;
    }
}
