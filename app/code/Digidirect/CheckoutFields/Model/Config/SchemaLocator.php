<?php
namespace Digidirect\CheckoutFields\Model\Config;

use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Config\Dom\UrnResolver;

/**
 * Class SchemaLocator
 * @package Digidirect\CheckoutFields\Model\Config
 */
class SchemaLocator implements SchemaLocatorInterface
{
    /**
     * @var UrnResolver
     */
    protected $_urnResolver;

    /**
     * SchemaLocator constructor.
     * @param \Magento\Framework\Config\Dom\UrnResolver $urnResolver
     */
    public function __construct(UrnResolver $urnResolver)
    {
        $this->_urnResolver = $urnResolver;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        $path =  $this->_urnResolver->getRealPath('urn:digidirect:module:Digidirect_CheckoutFields:etc/checkout_fields.xsd');
        return $path;
    }

    /**
     * @return string
     */
    public function getPerFileSchema()
    {
        return $this->_urnResolver->getRealPath('urn:digidirect:module:Digidirect_CheckoutFields:etc/checkout_fields.xsd');
    }
}
