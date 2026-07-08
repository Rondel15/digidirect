<?php

namespace Digidirect\CheckoutFields\Helper\Xml\Fields;

use Digidirect\CheckoutFields\Helper\Xml\Fields\Parser;

/**
 * Class Pdp
 * @package Digidirect\CheckoutFields\Helper\Xml\Fields
 */
class Pdp
{
    /**
     * @var Parser
     */
    protected $_parser;

    /**
     * Pdp constructor.
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->_parser = $parser;
    }

    /**
     * @return array|null
     */
    public function getPDPCustomFields()
    {
        return $this->_parser->getFields(null, true);
    }
}
