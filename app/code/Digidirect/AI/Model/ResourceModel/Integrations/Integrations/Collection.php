<?php

namespace Digidirect\AI\Model\ResourceModel\Integrations\Integrations;

use Magento\Framework\DataObject;
use Digidirect\AI\Model;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection as AbstractCollection;

/**
 * @method \Digidirect\AI\Model\ResourceModel\Integrations\Integrations getResource()
 */
class Collection extends AbstractCollection // implements DocumentInterface
{
    /* Used only for grid paging / selection working */

    /**
     * Field name id
     *
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * @var array
     */
    protected $confIntegrationItems;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Model\Integrations\Config\Data $integrationsConfig
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\App\Helper\Context $context,
        \Digidirect\AI\Model\Integrations\Config\Data $integrationsConfig,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $context->getLogger(),
            $fetchStrategy,
            $context->getEventManager(),
            $connection,
            $resource
        );
        $this->confIntegrationItems = $integrationsConfig->getAllIntegrations();
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Digidirect\AI\Model\Integrations\Integrations::class,
            \Digidirect\AI\Model\ResourceModel\Integrations\Integrations::class
        );
    }

    /**
     * Redeclare after load method for specifying integration data from integrations config nodes
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        $this->_completeData();
        return $this;
    }

    /**
     * @throws \Exception
     * @return void
     */
    protected function _completeData()
    {
        $resource = $this->getResource();
        $checked = [];
        foreach ($this->_items as $k => $integration) {
            $checked[$integration->getData('process_code')] = true;
            if ($integration->getSkip()) {
                $this->removeItemByKey($k);
                continue;
            }
            $resource->completeData($integration);
        }

        foreach ($this->confIntegrationItems as $processCode => $data) {
            if (isset($checked[$processCode])) {
                continue;
            }

            $integration = $this->getNewEmptyItem();
            $resource->completeData($integration, $processCode);
            $this->addItem($integration);
        }
    }
}
