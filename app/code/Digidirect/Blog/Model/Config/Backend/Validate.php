<?php

namespace Digidirect\Blog\Model\Config\Backend;

use Magento\Framework\View\Model\Layout\Update\ValidatorFactory;

/**
 * Class Validate
 */
class Validate extends \Magento\Framework\App\Config\Value
{
    /**
     * Layout update validator factory
     *
     * @var ValidatorFactory
     */
    protected $layoutUpdateValidatorFactory;

    /**
     * Validate constructor.
     * @param ValidatorFactory $layoutUpdateValidatorFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        ValidatorFactory $layoutUpdateValidatorFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->layoutUpdateValidatorFactory = $layoutUpdateValidatorFactory;
    }

    /**
     * @return \Magento\Framework\App\Config\Value|void
     */
    public function beforeSave()
    {
        $xml = $this->getValue();
        if ($xml !== '') {
            $validator = $this->layoutUpdateValidatorFactory->create();
            $validator->isValid($xml);
        }
        parent::beforeSave();
    }
}
