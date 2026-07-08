<?php

namespace Digidirect\AbstractGiftCard\Service\Config;

use Magento\Framework\ObjectManagerInterface;
use Digidirect\AbstractGiftCard\Service\ConfigFactoryInterface;

class ConfigFactory implements ConfigFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $_om;

    /**
     * ConfigFactory constructor.
     * @param ObjectManagerInterface $om
     */
    public function __construct(
        ObjectManagerInterface $om
    ) {
        $this->_om = $om;
    }

    /**
     * @param string|null $serviceCode
     * @param string|null $pathPattern
     * @return mixed
     */
    public function create($serviceCode = null, $pathPattern = null)
    {
        $arguments = [
            'serviceCode' => $serviceCode
        ];

        if ($pathPattern !== null) {
            $arguments['pathPattern'] = $pathPattern;
        }

        return $this->_om->create(Config::class, $arguments);
    }
}
