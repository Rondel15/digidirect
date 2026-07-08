<?php
namespace Digidirect\CheckoutFields\Model\Config\Source;

use Digidirect\CheckoutFields\Helper\Xml\Fields\Parser;

/**
 * Class Fields
 *
 * @package Digidirect\CheckoutFields\Model\Config\Source
 */
class Fields implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Digidirect\CheckoutFields\Helper\Xml\Fields\Parser
     */
    protected $parser;

    /**
     * @var array
     */
    protected $fields;

    /**
     * Fields constructor.
     *
     * @param \Digidirect\CheckoutFields\Helper\Xml\Fields\Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->fields) {
            $fields = $this->parser->getAllFields();
            foreach ($fields as $fieldCode => $fieldData) {
                $this->fields[] = [
                    'value' => $fieldCode,
                    'label' => $fieldData['frontend_name']
                ];
            }
        }

        return $this->fields;
    }
}
