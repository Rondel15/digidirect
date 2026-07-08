<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * @category   BSS
 * @package    Bss_PreOrder
 * @author     Extension Team
 * @copyright  Copyright (c) 2025 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Model\MSI;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Module\Manager;

class MsiObjectManagement
{
    const MSI_MODULE_CORE = 'Magento_Inventory';

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManagerFactory;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var null
     */
    protected $stockIdForWebsite;

    /**
     * @var null
     */
    protected $defaultStock;

    /**
     * @param ObjectManagerInterface $objectManagerFactory
     * @param Manager $moduleManager
     * @param $stockIdForWebsite
     * @param $defaultStock
     */
    public function __construct(
        ObjectManagerInterface $objectManagerFactory,
        Manager $moduleManager,
        $stockIdForWebsite = null,
        $defaultStock = null
    ) {
        $this->objectManagerFactory = $objectManagerFactory;
        $this->moduleManager = $moduleManager;
        $this->stockIdForWebsite = $stockIdForWebsite;
        $this->defaultStock = $defaultStock;
    }

    /**
     * Is enable MSI  Core
     *
     * @return bool
     */
    public function isMsiEnabled()
    {
        return $this->moduleManager->isEnabled(self::MSI_MODULE_CORE);
    }

    /**
     * Get object instance
     *
     * @param $objectName
     * @param array $data
     * @return object|null
     */
    public function getObjectInstance($objectName, $data = [])
    {
        if ($this->isMsiEnabled()) {
            return $this->objectManagerFactory->create(
                $objectName,
                $data
            );
        }
        return null;
    }

    /**
     * Get instance stock for website
     *
     * @param array $data
     * @return object|null
     */
    public function getStockIdForWebsite($data = [])
    {
        return $this->getObjectInstance(
            $this->stockIdForWebsite,
            $data
        );
    }

    /**
     * Get instance default stock
     *
     * @param array $data
     * @return object|null
     */
    public function getDefaultStock($data = [])
    {
        return $this->getObjectInstance(
            $this->defaultStock,
            $data
        );
    }
}
