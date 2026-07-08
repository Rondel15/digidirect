<?php
// @codingStandardsIgnoreFile

/**
 * Model factory
 */
namespace Digidirect\CheckoutFields\Model\Component\Type;

use \Magento\Framework\ObjectManagerInterface;
use \Digidirect\CheckoutFields\Model\Component\Type\AbstractType;
use \Magento\Framework\Exception\LocalizedException;

/**
 * Class Factory
 * @package Digidirect\CheckoutFields\Model\Component\Type
 */
class Factory
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Construct
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create model
     *
     * @param string $className
     * @param array $data
     * @return \Magento\Framework\Model\AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($className, array $data = [])
    {
        $model = $this->_objectManager->create($className, $data);
        if (!$model instanceof AbstractType) {
            throw new LocalizedException(
                __('%1 doesn\'t extends \Digidirect\CheckoutFields\Model\Component\Type\AbstractType', $className)
            );
        }
        return $model;
    }
}
