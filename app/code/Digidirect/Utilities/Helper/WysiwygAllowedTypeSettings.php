<?php

namespace Digidirect\Utilities\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class WysiwygSettings
 *
 * @package Digidirect\Utilities\Helper
 */
class WysiwygAllowedTypeSettings extends AbstractHelper
{
    const XML_PATH_ALLOWED_FILETYPES = 'digidirect_utilities_config/wysiwyg/allowed_filetypes';
    const XML_PATH_ALLOWED_TAGS = 'digidirect_utilities_config/wysiwyg/allowed_tags';
    const XML_PATH_WRAP_IN_PARENT_TAG = 'digidirect_utilities_config/wysiwyg/wrap_in_parent_tag';
    const XML_PATH_ALLOWED_CHILDS  = 'digidirect_utilities_config/wysiwyg/allowed_childs';
    const ALLOWED_FILE_TYPES_KEY_EXTENSION = 'extension';
    const ALLOWED_FILE_TYPES_KEY_MIME = 'mimetype';

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var array
     */
    protected $extraFileTypes = [];

    /**
     * Settings constructor.
     *
     * @param Context $context
     * @param Json    $serializer
     */
    public function __construct(
        Context $context,
        Json $serializer
    ) {
        parent::__construct($context);
        $this->serializer = $serializer;
    }

    /**
     * @return array
     */
    public function getAdditionalFiletypes()
    {
        return $this->extractFileTypeListByColumn(self::ALLOWED_FILE_TYPES_KEY_EXTENSION);
    }

    /**
     * @return array
     */
    public function getAdditionalMimetypes()
    {
        return $this->extractFileTypeListByColumn(self::ALLOWED_FILE_TYPES_KEY_MIME);
    }

    /**
     * @param array $pathInfo
     *
     * @return bool
     */
    public function isAdditionalFiletype(array $pathInfo)
    {
        return array_key_exists(self::ALLOWED_FILE_TYPES_KEY_EXTENSION, $pathInfo)
            && in_array($pathInfo[self::ALLOWED_FILE_TYPES_KEY_EXTENSION], $this->getAdditionalFiletypes(), true);
    }

    /**
     * Return allowed tags string
     * @return string
     */
    public function getAllowedTags()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ALLOWED_TAGS);
    }

    /**
     * Return is parent tag should be wrapped
     * @return bool
     */
    public function hasWrapInParentTag()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_WRAP_IN_PARENT_TAG);
    }

    /**
     * Return allowed childs string
     * @return string
     */
    public function getAllowedChilds()
    {

        return $this->scopeConfig->getValue(self::XML_PATH_ALLOWED_CHILDS);
    }

    /**
     * @return mixed
     */
    protected function getAdditionalAllowedFileTypes()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_ALLOWED_FILETYPES);
    }

    /**
     * @param string $type
     *
     * @return array
     */
    protected function extractFileTypeListByColumn(string $type)
    {
        if (isset($this->extraFileTypes[$type])) {
            return $this->extraFileTypes[$type];
        }
        $this->extraFileTypes[$type] = [];

        $additionalAllowedFileTypes = $this->getAdditionalAllowedFileTypes();
        if (!$additionalAllowedFileTypes) {
            return $this->extraFileTypes[$type];
        }
        $settings = $this->serializer->unserialize($additionalAllowedFileTypes);

        foreach ($settings as $setting) {
            if (!empty($setting[$type])) {
                $this->extraFileTypes[$type][] = $setting[$type];
            }
        }

        return $this->extraFileTypes[$type];
    }
}
