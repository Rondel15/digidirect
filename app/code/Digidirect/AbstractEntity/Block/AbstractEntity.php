<?php

namespace Digidirect\AbstractEntity\Block;

use Digidirect\AbstractEntity\Helper\Image;
use Digidirect\AbstractEntity\Model\Registry\Constants;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

class AbstractEntity extends Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @param Registry $coreRegistry
     * @param Image $imageHelper
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        Image $imageHelper,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Get current abstract entity
     *
     * @return \Digidirect\AbstractEntity\Model\AbstractEntity|null
     */
    public function getCurrentAbstractEntity()
    {
        return $this->coreRegistry->registry(Constants::CURRENT_ABSTRACT_ENTITY);
    }

    /**
     * Get image helper
     *
     * @return Image
     */
    public function getImageHelper()
    {
        return $this->imageHelper;
    }
}
