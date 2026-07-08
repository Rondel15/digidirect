<?php

namespace Digidirect\StoreLocator\Model\Frontend;

use Digidirect\StoreLocator\Helper\Config;

/**
 * Used as a wrapper for all getUrl function calls
 */
class UrlModifier
{
    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @var null|bool
     */
    private $modifyFlag = null;

    /**
     * UrlModifier constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->configHelper = $config;
    }

    /**
     * @param string $url
     * @return string
     */
    public function modify(string $url): string
    {
        if (null === $this->modifyFlag) {
            $this->modifyFlag = $this->configHelper->removeTrailingSlash();
        }

        if ($this->modifyFlag) {
            $url = \rtrim($url, '/');
        }

        return $url;
    }
}
