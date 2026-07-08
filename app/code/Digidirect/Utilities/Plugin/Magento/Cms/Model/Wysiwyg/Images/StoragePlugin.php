<?php

namespace Digidirect\Utilities\Plugin\Magento\Cms\Model\Wysiwyg\Images;

use Digidirect\Utilities\Helper\WysiwygAllowedTypeSettings;
use Magento\Cms\Model\Wysiwyg\Images\Storage;

/**
 * Class StoragePlugin
 * @package Digidirect\Utilities\Plugin\Magento\Cms\Model\Wysiwyg\Images
 */
class StoragePlugin
{
    /**
     * @var WysiwygAllowedTypeSettings
     */
    protected $settings;

    /**
     * StoragePlugin constructor.
     * @param WysiwygAllowedTypeSettings $helperSettings
     */
    public function __construct(WysiwygAllowedTypeSettings $helperSettings)
    {
        $this->settings = $helperSettings;
    }

    /**
     * @param Storage $subject
     * @param array   $result
     * @param null    $type
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllowedExtensions(Storage $subject, $result, $type = null)
    {
        return array_merge($result, $this->settings->getAdditionalFiletypes());
    }
}
