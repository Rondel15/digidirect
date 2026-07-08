<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Backend\Integration;

class ActiveCampaignList extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Plumrocket\Newsletterpopup\Model\Integration\ActiveCampaign
     */
    private $activeCampaign;

    /**
     * ActiveCampaignList constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Plumrocket\Newsletterpopup\Model\Integration\ActiveCampaign $activeCampaign
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Plumrocket\Newsletterpopup\Model\Integration\ActiveCampaign $activeCampaign,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->activeCampaign = $activeCampaign;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return \Magento\Framework\App\Config\Value
     */
    public function beforeSave()
    {
        $value = $this->getValue();

        if (empty($value)) {
            $result = $this->activeCampaign->getAllLists();

            if (! empty($result) && is_array($result)) {
                $this->setValue(array_keys($result));
            }
        }

        return parent::beforeSave();
    }
}
