<?php

namespace Digidirect\Utilities\Plugin\Magento\Framework\File;

use Digidirect\Utilities\Helper\WysiwygAllowedTypeSettings;
use Magento\Framework\File\Uploader;

/**
 * Class UploaderPlugin
 *
 * @package Digidirect\Utilities\Magento\Framework\File
 */
class UploaderPlugin
{
    /**
     * @var WysiwygAllowedTypeSettings
     */
    protected $settings;

    /**
     * StoragePlugin constructor.
     *
     * @param WysiwygAllowedTypeSettings $helperSettings
     */
    public function __construct(WysiwygAllowedTypeSettings $helperSettings)
    {
        $this->settings = $helperSettings;
    }

    /**
     * @param Uploader $subject
     * @param array    $validTypes
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCheckMimeType(Uploader $subject, $validTypes = [])
    {
        $validTypes = array_merge($validTypes, $this->settings->getAdditionalMimetypes());

        return [$validTypes];
    }
}
