<?php
namespace Digidirect\Utilities\Model\System\Config\Backend;

/**
 * Class MessageIdentifiers
 * @package Digidirect\Utilities\Model\System\Config\Backend
 */
class MessageIdentifiers extends \Magento\Framework\App\Config\Value
{
    /**
     * @var null
     */
    protected $_messageIdentifiersHelper = null;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Digidirect\Utilities\Helper\Message $_messageIdentifiersHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Digidirect\Utilities\Helper\Message $_messageIdentifiersHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_messageIdentifiersHelper = $_messageIdentifiersHelper;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Process data after load
     * @return void
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        $value = $this->_messageIdentifiersHelper->makeArrayFieldValue($value);
        $this->setValue($value);
    }

    /**
     * Prepare data before save
     * @return void
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $value = $this->_messageIdentifiersHelper->makeStorableArrayFieldValue($value);
        $this->setValue($value);
    }
}
