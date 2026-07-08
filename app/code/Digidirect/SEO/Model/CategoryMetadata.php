<?php
namespace Digidirect\SEO\Model;

use Digidirect\SEO\Helper\Category as CategorySEOHelper;

/**
 * Class CategoryMetadata
 * @package Digidirect\SEO\Model
 */
class CategoryMetadata implements MetadataInterface
{
    const MAX_META_DESCRIPTION_LENGTH = 160;

    /**
     * @var CategorySEOHelper
     */
    protected $helper;

    /**
     * Config array
     *
     *
     * ['meta_field_code' => 'copy_value_from_this_field_if_empty']
     *
     *
     * @var []
     */
    protected $metadataConfig;

    /**
     * CategoryMetadata constructor.
     *
     * @param CategorySEOHelper $helper
     * @param array $metadataConfig
     */
    public function __construct(
        CategorySEOHelper $helper,
        array $metadataConfig = []
    ) {
        $this->helper = $helper;
        $this->metadataConfig = $metadataConfig;
    }

    /**
     * Check if functionality is currently enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->helper->isMetaAutoGenerationEnabled();
    }

    /**
     * Modify object data - add metadata if empty
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function setMetadata(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$this->isEnabled()) {
            return $object;
        }

        foreach ($this->metadataConfig as $metaFieldName => $dataFieldCode) {
            if (!$object->getData($metaFieldName)) {
                $object->setData($metaFieldName, $this->_stripTagsAndLimit($object->getData($dataFieldCode)));
            }
        }
        return $object;
    }

    /**
     * Remove tags and cut to 160 symbols
     *
     * @param string $string
     * @return string
     */
    protected function _stripTagsAndLimit($string = '')
    {
        if(empty($string))
        {
            return $string;
        }
        
        return substr(strip_tags($string), 0, self::MAX_META_DESCRIPTION_LENGTH);
    }
}
