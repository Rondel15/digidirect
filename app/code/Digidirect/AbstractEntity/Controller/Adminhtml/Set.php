<?php
namespace Digidirect\AbstractEntity\Controller\Adminhtml;

use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity as AbstractEntityResource;
use Digidirect\AbstractEntity\Model\Registry\Constants;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

abstract class Set extends Action
{
    const ADMIN_RESOURCE = 'Digidirect_AbstractEntity::abstractentity_view';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $abstractEntityResource;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param AbstractEntityResource $abstractEntityResource
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        AbstractEntityResource $abstractEntityResource
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->abstractEntityResource = $abstractEntityResource;
        parent::__construct($context);
    }

    /**
     * Define in register catalog_product entity type code as entityType
     *
     * @return void
     */
    protected function _setTypeId()
    {
        $this->_coreRegistry->register(
            Constants::ENTITY_TYPE,
            $this->abstractEntityResource->getTypeId()
        );
    }
}
