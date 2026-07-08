<?php
namespace Digidirect\SEO\Model;

/**
 * Interface MetadataInterface
 * @package Digidirect\SEO\Model
 */
interface MetadataInterface
{
    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function setMetadata(\Magento\Framework\Model\AbstractModel $object);

    /**
     * @return bool
     */
    public function isEnabled();
}
