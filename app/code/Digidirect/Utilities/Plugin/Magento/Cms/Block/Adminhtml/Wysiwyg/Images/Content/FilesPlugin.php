<?php

namespace Digidirect\Utilities\Plugin\Magento\Cms\Block\Adminhtml\Wysiwyg\Images\Content;

use Digidirect\Utilities\Helper\WysiwygAllowedTypeSettings;
use Magento\Cms\Block\Adminhtml\Wysiwyg\Images\Content\Files;
use Magento\Framework\DataObject;

/**
 * Class FilesPlugin
 * @package Digidirect\Utilities\Plugin\Magento\Cms\Block\Adminhtml\Wysiwyg\Images\Content
 */
class FilesPlugin
{
    /**
     * @var WysiwygAllowedTypeSettings
     */
    protected $settings;

    /**
     * FilesPlugin constructor.
     * @param WysiwygAllowedTypeSettings $helperSettings
     */
    public function __construct(WysiwygAllowedTypeSettings $helperSettings)
    {
        $this->settings = $helperSettings;
    }

    /**
     * @param Files      $subject
     * @param \Closure   $proceed
     * @param DataObject $file
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetFileThumbUrl(Files $subject, \Closure $proceed, DataObject $file)
    {
        $pathInfo = pathinfo($file->getData('filename'));
        if ($pathInfo && !$this->settings->isAdditionalFiletype($pathInfo)) {
            return $proceed($file);
        }

        return '';
    }
}
