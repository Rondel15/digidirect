<?php
namespace Digidirect\CollectAbstractEntityMsi\Helper;

use Digidirect\CollectAbstractEntityMsi\Api\Data\CollectFields\Constants;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 * @package Digidirect\CollectAbstractEntityMsi\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var \Digidirect\CollectAbstractEntity\Helper\Config
     */
    protected $configHelper;

    /**
     * Data constructor.
     * @param Context $context
     * @param \Digidirect\CollectAbstractEntity\Helper\Config $configHelper
     */
    public function __construct(
        Context $context,
        \Digidirect\CollectAbstractEntity\Helper\Config $configHelper
    ) {
        parent::__construct($context);
        $this->configHelper = $configHelper;
    }

    /**
     * Get MSI Inventory Source attribute code from Abstract Entity, selected in configuration matrix.
     * @return string|bool
     */
    public function getSourceInventoryAttributeFromConfig()
    {
        $matrix = $this->configHelper->getCollectFieldsMatrix();
        return $matrix[Constants::COLLECT_FIELD_INVENTORY_SOURCE] ?? false;
    }
}
