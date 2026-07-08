<?php

namespace Digidirect\CheckoutFields\Block\Checkout\Custom;

use Digidirect\CheckoutFields\Helper\Xml\Fields\Parser;
use Digidirect\CheckoutFields\Model\Checkout\Provider\CustomCheckoutFields\CompositeConfigProvider;
use Digidirect\CheckoutFields\Model\Component\Type\AbstractType;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Fields
 *
 * @package Digidirect\CheckoutFields\Block\Checkout\Custom
 */
class Fields extends Template
{
    const SERVICE_URL = 'serviceUrl';
    const FIELDS = 'fields';
    const PROVIDER = 'provider';

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var Parser|null
     */
    protected $parser = null;

    /**
     * @var \Digidirect\CheckoutFields\Model\Checkout\Provider\CustomCheckoutField\CompositeConfigProvider
     */
    protected $configProvider;

    /**
     * Fields constructor.
     *
     * @param Context $context
     * @param Data $jsonHelper
     * @param Parser $parser
     * @param \Digidirect\CheckoutFields\Model\Checkout\Provider\CustomCheckoutField\CompositeConfigProvider $configProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $jsonHelper,
        Parser $parser,
        CompositeConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->jsonHelper = $jsonHelper;
        $this->parser = $parser;
        $this->configProvider = $configProvider;
    }

    /**
     * @return string
     */
    public function getFields()
    {
        return $this->jsonHelper->jsonEncode($this->getParserFields());
    }

    /**
     * @return string
     */
    public function getServiceUrl()
    {
        return '/checkoutfields/save';
    }

    /**
     * @return array|null
     */
    public function getParserFields()
    {
        $fields = $this->parser->getFields();

        $fieldsNew = [];
        if (!empty($fields)) {
            foreach ($fields as $key => $field) {
                $step = $field['area']['checkout_step'] ?? '';
                $fieldsNew[$step][$key] = $fields[$key];
            }
        }

        return $fieldsNew;
    }

    /**
     * @return string
     */
    public function getProvider()
    {
        return AbstractType::PROVIDER_VALUE;
    }

    /**
     * @return bool|string
     */
    public function getSerializedCheckoutFieldsConfig()
    {
        return json_encode($this->getCustomCheckoutFieldsConfig(), JSON_HEX_TAG);
    }

    /**
     * @return array
     */
    public function getCustomCheckoutFieldsConfig()
    {
        return array_merge_recursive(
            [
                self::SERVICE_URL => $this->getServiceUrl(),
                self::FIELDS => $this->getParserFields(),
                self::PROVIDER => $this->escapeHtml($this->getProvider())
            ],
            $this->configProvider->getConfig()
        );
    }
}
