<?php

namespace Digidirect\CheckoutFields\Model\Checkout\Provider;

use Digidirect\CheckoutFields\Helper\Xml\Fields\Parser;

/**
 * Class CheckoutConfigProvider
 * @package Digidirect\CheckoutFields\Model\Checkout\Provider
 */
class CheckoutConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    const CUSTOM_CHECKOUT_FIELDS_CONFIG_PROVIDER = 'custom_checkout_fields_config_provider';
    const CUSTOM_CHECKOUT_FIELDS_DEPENDENCY = 'custom_checkout_fields_dependency';

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * CheckoutConfigProvider constructor.
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        $fieldsDependancy = $this->getFieldsDependancy();
        if (!empty($fieldsDependancy)) {
            $config[static::CUSTOM_CHECKOUT_FIELDS_CONFIG_PROVIDER] = [
                static::CUSTOM_CHECKOUT_FIELDS_DEPENDENCY => $fieldsDependancy
            ];
        }
        return $config;
    }

    /**
     * @return array
     */
    public function getFieldsDependancy()
    {
        $fieldsDependancy = [];
        $fields = $this->parser->getAllFields();
        foreach ($fields as $key => $field) {
            $dependency = $this->parser->getDependsField($field);
            if ($dependency) {
                $fieldsDependancy[$key] = [
                    'depends' => $dependency
                ];
            }
        }

        return $fieldsDependancy;
    }
}
