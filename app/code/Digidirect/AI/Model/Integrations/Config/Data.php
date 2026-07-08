<?php
namespace Digidirect\AI\Model\Integrations\Config;

class Data extends \Magento\Framework\Config\Data
{
    const INTEGRATIONS_SECTION = 'integrations';

    /**
     * @var array
     */
    protected $_integrations;

    /**
     * @return array
     */
    public function getAllIntegrations()
    {
        if ($this->_integrations === null) {
            $this->_integrations = [];
            $configData = $this->get(self::INTEGRATIONS_SECTION);
            if (is_array($configData)) {
                foreach ($configData as $name => $integration) {
                    if (is_array($integration)) {
                        $integration['indexers'] = $this->prepareConfigProperties($integration, 'indexers');
                        $integration['cache'] = $this->prepareConfigProperties($integration, 'cache');
                        $integration['mviews'] = $this->prepareConfigProperties($integration, 'mviews');
                        $this->_integrations[$name] = $integration;
                    }
                }
            }
        }
        return $this->_integrations;
    }

    /**
     * @param string $name
     * @return bool|array
     */
    public function getIntegrationByName($name)
    {
        $integrations = $this->getAllIntegrations();
        if (isset($integrations[$name])) {
            return $integrations[$name];
        }
        return false;
    }

    /**
     * @param array $configs
     * @param mixed $key
     * @return array
     */
    protected function prepareConfigProperties($configs, $key)
    {
        if (isset($configs[$key])) {
            return array_keys($configs[$key]);
        }
        return [];
    }
}
