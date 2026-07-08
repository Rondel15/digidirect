<?php
namespace Digidirect\Utilities\Block;

use Magento\Framework\View\Element\Template;
use Digidirect\Utilities\Helper\WysiwygAllowedTypeSettings as WysiwygSettings;

/**
 * Class Wysiwyg
 * @package Digidirect\Utilities\Block
 * @deprecated Please use: \Digidirect\Utilities\Helper\WysiwygAllowedTypeSettings
 */
class Wysiwyg extends Template
{
    /**
     * @deprecated constants
     */
    const XML_PATH_ALLOWED_TAGS = 'digidirect_utilities_config/wysiwyg/allowed_tags';
    const XML_PATH_WRAP_IN_PARENT_TAG = 'digidirect_utilities_config/wysiwyg/wrap_in_parent_tag';
    const XML_PATH_ALLOWED_CHILDS  = 'digidirect_utilities_config/wysiwyg/allowed_childs';

    /**
     * @var WysiwygSettings
     */
    protected $wysiwygSettings;

    /**
     * Wysiwyg constructor.
     * @param Template\Context $context
     * @param WysiwygSettings $wysiwygSettings
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        WysiwygSettings $wysiwygSettings,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->wysiwygSettings = $wysiwygSettings;
    }

    /**
     * Return allowed tags string
     * @return string
     */
    public function getAllowedTags()
    {
        return $this->wysiwygSettings->getAllowedTags();
    }

    /**
     * Return is parent tag should be wrapped
     * @return bool
     */
    public function hasWrapInParentTag()
    {
        return $this->wysiwygSettings->hasWrapInParentTag();
    }

    /**
     * Return allowed childs string
     * @return string
     */
    public function getAllowedChilds()
    {
        return $this->wysiwygSettings->getAllowedChilds();
    }
}
