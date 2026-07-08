<?php
namespace Digidirect\AI\Model\Integrations\Rule;

use Magento\Framework\ObjectManagerInterface;

class RulesRepository
{
    /**
     * @var \Digidirect\AI\Model\Integrations\Config\Data
     */
    protected $_integrationsConfig;

    /**
     * RuleRepository constructor.
     * @param ObjectManagerInterface $objectManager
     * @param \Digidirect\AI\Model\Integrations\Config\Data $integrationsConfig
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        \Digidirect\AI\Model\Integrations\Config\Data $integrationsConfig
    ) {
        $this->_objectManager = $objectManager;
        $this->_integrationsConfig = $integrationsConfig;
    }

    /**
     * @param string $integrationName
     * @return array
     */
    public function getRulesByIntegrationName($integrationName)
    {
        $rules = [];
        $integration = $this->_integrationsConfig->getIntegrationByName($integrationName);
        foreach ($integration['entity_rules'] as $entityRule) {
            if (isset($entityRule['mapper'])) {
                $mapperInstance = $entityRuleObject = $this->_objectManager->create($entityRule['mapper']);
                $entityRule['mapper'] = $mapperInstance;
            }
            $entityRuleObject = $this->_objectManager->create($entityRule['class'], $entityRule);
            if ($entityRuleObject instanceof RuleAbstract) {
                $rules[] = $entityRuleObject;
            }
        }

        return $rules;
    }
}
