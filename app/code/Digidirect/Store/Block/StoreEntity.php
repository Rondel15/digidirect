<?php
namespace Digidirect\Store\Block;

use Digidirect\AbstractEntity\Helper\Image;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class StoreEntity
 * @package Digidirect\AbstractEntity\Block
 */
class StoreEntity extends \Digidirect\AbstractEntity\Block\AbstractEntity
{
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * StoreEntity constructor.
     * @param Registry $coreRegistry
     * @param Image $imageHelper
     * @param Context $context
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        Image $imageHelper,
        Context $context,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        array $data
    ) {
        $this->_countryFactory = $countryFactory;
        parent::__construct($coreRegistry, $imageHelper, $context, $data);
    }

    /**
     * Retrieve entity country name
     *
     * @param string $countryCode
     * @return string
     */
    public function getCountryName($countryCode)
    {
        if ($countryCode) {
            $country = $this->_countryFactory->create()->loadByCode($countryCode);
            return $country->getName();
        }
        return '';
    }
}
