<?php

namespace Digidirect\AI\Model\Config\Reader\Source\Dynamic;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Module\ModuleResource;
use Digidirect\AI\Cron\RunProcessByCron;
use Digidirect\AI\Model\ResourceModel\Integrations\Integrations\Collection;
use Digidirect\AI\Model\ResourceModel\Integrations\Integrations\CollectionFactory;
use Magento\Framework\App\Config\Reader\Source\SourceInterface;

/**
 * Class for retrieving configuration from DB by default scope
 */
class DefaultScope implements SourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected $integrationsCollectionFactory;

    /**
     * @var ModuleResource
     */
    protected $moduleResource;

    /**
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * DefaultScope constructor.
     *
     * @param CollectionFactory $integrationsCollection
     * @param ModuleResource $moduleResource
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        CollectionFactory $integrationsCollection,
        ModuleResource $moduleResource,
        DeploymentConfig $deploymentConfig
    ) {
        $this->integrationsCollectionFactory = $integrationsCollection;
        $this->moduleResource = $moduleResource;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Retrieve config by default scope
     *
     * @param string|null $scopeCode
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get($scopeCode = null)
    {
        //if module not installed yet this class is loaded and tries to use not created database table.
        // deployment config - AI requires db connection during setup:di:compile - that is wrong behaviour
        if (!$this->deploymentConfig->isDbAvailable() || !$this->moduleResource->getDbVersion('Digidirect_AI')) {
            return [];
        }

        $items = $this->integrationsCollectionFactory->create()->getItems();
        $config = [];
        foreach ($items as $item) {
            $cronTime = trim($item->getCronTime());
            if (!$cronTime || !preg_match(\Digidirect\AI\Helper\Data::CRON_REGEXP, $cronTime)) {
                continue;
            }

            $processCode = $item->getProcessCode();
            $config['default']['crontab']['digidirect_ai']['jobs'][$processCode] = [
                'name' => $processCode,
                'instance' => RunProcessByCron::class,
                'method' => $processCode,
                'schedule' => $item->getCronTime(),
            ];
        }
        return $config;
    }
}
