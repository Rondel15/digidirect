<?php
namespace Digidirect\Utilities\Plugin\Magento\Cms\Model\Wysiwyg;

use Digidirect\Utilities\Helper\WysiwygAllowedTypeSettings;
use Magento\Cms\Model\Wysiwyg\Config as Subject;

/**
 * Class ConfigPlugin
 * @package Digidirect\Utilities\Plugin\Magento\Cms\Model\Wysiwyg
 */
class ConfigPlugin
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
     * @param Subject $subject
     * @param array $data
     * @return array
     */
    public function beforeGetConfig(Subject $subject, $data = [])
    {
        if ($allowedChilds = $this->settings->getAllowedChilds()) {
            $data['settings']['valid_children'] = $allowedChilds;
        }

        if ($allowedTags = $this->settings->getAllowedTags()) {
            $data['settings']['extended_valid_elements'] = $allowedTags;
        }

        if (!$this->settings->hasWrapInParentTag()) {
            $data['settings']['forced_root_block'] = false;
        }

        return [$data];
    }
}
