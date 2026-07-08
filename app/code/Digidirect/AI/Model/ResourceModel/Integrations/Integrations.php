<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Digidirect\AI\Model\ResourceModel\Integrations;

/**
 * Class Integrations
 * @package Digidirect\AI\Model\ResourceModel\Integrations
 */
class Integrations extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var array
     */
    protected $confIntegrationItems;

    /**
     * @var null
     */
    protected $processCode = null;

    /**
     * Integrations constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Digidirect\AI\Model\Integrations\Config\Data $integrationsConfig
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Digidirect\AI\Model\Integrations\Config\Data $integrationsConfig,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->confIntegrationItems = $integrationsConfig->getAllIntegrations();
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('digidirect_ai_integrations', 'id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param null $field
     * @return void
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if ($field == 'process_code') {
            $this->processCode = $value;
        } else {
            $this->processCode = null;
        }

        parent::load($object, $value, $field);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterLoad($object);
        $this->completeData($object, $this->processCode);
    }

    /**
     * Used for completing data loading from collection
     * @param \Magento\Framework\Model\AbstractModel $integration
     * @param string|null $processCode
     * @return void
     */
    public function completeData($integration, $processCode = null)
    {
        $isObjectNew = !$integration->getId();
        $processCode = $integration->getProcessCode() ? $integration->getProcessCode() : $processCode;
        if (!$processCode || !isset($this->confIntegrationItems[$processCode])) {
            return;
        }

        $data = $this->confIntegrationItems[$processCode];

        $integration->setData('process_class', $data['processor_class']);
        $integration->setData('process_code', $processCode);
        $integration->setData('integration_name', $data['integration_name']);
        $integration->setData('can_admin_run', $data['can_admin_run'] ?? 0);
        $integration->setData('multiple_run', $data['multiple_run'] ?? 0);
        $integration->setData('continue_throw', $data['continue_throw'] ?? 0);

        if (isset($data['run_options'])) {
            $integration->setData('run_options', $data['run_options']);
        }

        if ($isObjectNew) {
            $integration->setData('status', \Digidirect\AI\Model\Integrations\Integrations::STATUS_PENDING);
            $integration->setData('cron_time', $data['cron_time'] ?? '');
            $integration->setData('child_process_code', $data['chain_proccessor_code'] ?? '');
        }
    }
}
