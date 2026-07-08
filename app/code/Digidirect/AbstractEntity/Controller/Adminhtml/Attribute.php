<?php
namespace Digidirect\AbstractEntity\Controller\Adminhtml;

use Digidirect\AbstractEntity\Model\AbstractEntity;
use Digidirect\AbstractEntity\Model\ResourceModel\Eav\AttributeFactory as EavAttributeFactory;
use Magento\Backend\App\Action;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Config\Proxy as EavConfigProxy;
use Magento\CustomAttributeManagement\Helper\Data as AttributeHelper;

abstract class Attribute extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Digidirect_AbstractEntity::abstractentity_attribute';

    /**
     * @var \Magento\Eav\Model\Entity\Type
     */
    protected $_entityType;

    /**
     * @var EavConfig
     */
    protected $_eavConfig;

    /**
     * @var EavAttributeFactory
     */
    protected $_eavAttributeFactory;

    /**
     * @var AttributeHelper
     */
    protected $_attributeHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param EavConfigProxy $eavConfig
     * @param EavAttributeFactory $eavAttributeFactory
     * @param AttributeHelper $attributeHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        EavConfigProxy $eavConfig,
        EavAttributeFactory $eavAttributeFactory,
        AttributeHelper $attributeHelper
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_eavConfig = $eavConfig;
        $this->_eavAttributeFactory = $eavAttributeFactory;
        $this->_attributeHelper = $attributeHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Eav\Model\Entity\Type
     */
    protected function _getEntityType()
    {
        if ($this->_entityType === null) {
            $this->_entityType = $this->_eavConfig->getEntityType(AbstractEntity::ENTITY_TYPE);
        }
        return $this->_entityType;
    }

    /**
     * Load layout, set breadcrumbs
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Digidirect_AbstractEntity::abstractentity_attribute'
        )->_addBreadcrumb(
            __('Abstract Entity'),
            __('Abstract Entity')
        )->_addBreadcrumb(
            __('Manage Abstract Entity Attributes'),
            __('Manage Abstract Entity Attributes')
        );
        return $this;
    }

    /**
     * @return \Digidirect\AbstractEntity\Model\ResourceModel\Eav\Attribute
     */
    protected function _initAttribute()
    {
        return $this->_eavAttributeFactory->create();
    }
}
