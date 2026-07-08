<?php

namespace Digidirect\CheckoutFields\Plugin\Checkout\Model;

use Digidirect\CheckoutFields\Api\Data\PdpFieldValueInterface;
use Digidirect\CheckoutFields\Model\Component\Type\AbstractType;
use Digidirect\CheckoutFields\Model\PdpFieldValueRepository;
use Magento\Checkout\Exception;
use \Digidirect\CheckoutFields\Helper\Xml\Fields\Parser;
use \Digidirect\CheckoutFields\Model\Component\Type\Factory;
use \Magento\Checkout\Block\Checkout\LayoutProcessor as MagentoLayoutProcessor;
use \Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class LayoutProcessor
 * @package Digidirect\CheckoutFields\Plugin\Checkout\Model
 */
class LayoutProcessor
{
    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var Factory
     */
    protected $typeFactory;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var PdpFieldValueRepository
     */
    protected $customFieldValueRepository;

    /**
     * @param Parser $parser
     * @param Factory $factory
     * @param CheckoutSession $session
     * @param PdpFieldValueRepository $pdpFieldValueRepository
     */
    public function __construct(
        Parser $parser,
        Factory $factory,
        CheckoutSession $session,
        PdpFieldValueRepository $pdpFieldValueRepository
    ) {
        $this->parser = $parser;
        $this->typeFactory = $factory;
        $this->checkoutSession = $session;
        $this->customFieldValueRepository = $pdpFieldValueRepository;
    }

    /**
     * @param MagentoLayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterProcess(MagentoLayoutProcessor $subject, array $jsLayout)
    {
        $fields = (array)$this->parser->getFields();
        foreach ($fields as $code => $options) {
            $step = $options[AbstractType::XML_AREA][AbstractType::XML_STEP];
            $scope = $options[AbstractType::XML_AREA][AbstractType::XML_SCOPE];
            $fieldset = $options[AbstractType::XML_AREA][AbstractType::XML_FIELDSET];
            if (!isset($jsLayout['components']['checkout']['children']['steps']['children'][$step]['children']
                [$scope]['children'][$fieldset]['children'])
            ) {
                continue;
            }
            $type = ucfirst(strtolower($options[AbstractType::XML_FRONTEND_INPUT]));
            try {
                /** @var \Digidirect\CheckoutFields\Model\Component\Type\AbstractType $model */
                $model = $this->typeFactory->create('\Digidirect\CheckoutFields\Model\Component\Type\\' . $type);
                if ($field = $model->getChild($code, $options)) {
                    $this->setCheckedForField($code, $field);
                    $jsLayout['components']['checkout']['children']['steps']['children'][$step]['children']
                    [$scope]['children'][$fieldset]['children'][$code] = $field;
                }
            } catch (Exception $e) {
                continue;
            }
        }
        return $jsLayout;
    }

    /**
     * @param string $code
     * @param array $field
     * @return void
     */
    public function setCheckedForField($code, &$field)
    {
        $quote = $this->checkoutSession->getQuote();

        if ($quoteId = $quote->getId()) {
            $last = $this->customFieldValueRepository->getListByQuoteId($quoteId, $code);
            if (!empty($last) && reset($last)->getData(PdpFieldValueInterface::FIELD_VALUE)) {
                $field['checked'] = true;
                $field['value'] = 1;
            }
        }
    }
}
