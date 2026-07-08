<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\ResourceModel\Popup;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;
use Plumrocket\Newsletterpopup\Api\Data\PopupInterface;
use Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface;
use Plumrocket\Newsletterpopup\Model\Popup;
use Plumrocket\Newsletterpopup\Model\ResourceModel\Popup as PopupResource;
use Psr\Log\LoggerInterface;

/**
 * @method Popup[]|PopupInterface[] getItems()
 */
class Collection extends AbstractCollection
{
    protected $_templateResource;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface    $entityFactory
     * @param \Psr\Log\LoggerInterface                                     $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager
     * @param \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme  $templateResource
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null          $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null    $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Theme $templateResource,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_templateResource = $templateResource;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init(Popup::class, PopupResource::class);
    }

    public function addStoreFilter($store)
    {
        if ($store instanceof Store) {
            $store = $store->getId();
        }

        if (is_array($store)) {
            $store = $store[0];
        }

        $this->getSelect()->where("FIND_IN_SET('{$store}', `store_id`) OR FIND_IN_SET('0', `store_id`)");

        return $this;
    }

    /**
     * @return $this
     */
    public function addThemeData(): Collection
    {
        $themeTableName = $this->_templateResource->getTable(Theme::MAIN_TABLE_NAME);

        $this->join(
            ['t' => $themeTableName],
            't.entity_id = main_table.template_id',
            ['name as template_name', 'base_template_id', PopupThemeInterface::HTML, PopupThemeInterface::CSS]
        );
        $this->getSelect()
            ->joinLeft(
                ['t2' => $themeTableName],
                't2.entity_id = t.base_template_id',
                ['base_template_name' => 'name']
            );

        return $this;
    }
}
