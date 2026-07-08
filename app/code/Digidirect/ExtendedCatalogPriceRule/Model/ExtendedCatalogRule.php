<?php
namespace Digidirect\ExtendedCatalogPriceRule\Model;

use Digidirect\ExtendedCatalogPriceRule\Api\Data\ExtendedCatalogRuleInterface;
use Digidirect\ExtendedCatalogPriceRule\Api\Data\RuleDisplayMessageInterface;
use Digidirect\ExtendedCatalogPriceRule\Model\ResourceModel\ExtendedCatalogRule as ExtendedCatalogRuleResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class ExtendedCatalogRule
 * @package Digidirect\ExtendedCatalogPriceRule\Model
 */
class ExtendedCatalogRule extends AbstractModel implements ExtendedCatalogRuleInterface, RuleDisplayMessageInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ExtendedCatalogRuleResource::class);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @return int
     */
    public function getRuleId()
    {
        return $this->getData(self::RULE_ID);
    }

    /**
     * @param integer $id
     * @return $this
     */
    public function setRuleId($id)
    {
        return $this->setData(self::RULE_ID, $id);
    }

    /**
     * @return string
     */
    public function getPlpLabel()
    {
        return $this->getData(self::PLP_LABEL);
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setPlpLabel($text)
    {
        return $this->setData(self::PLP_LABEL, $text);
    }

    /**
     * @return string
     */
    public function getPdpDescription()
    {
        return $this->getData(self::PDP_DESCRIPTION);
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setPdpDescription($text)
    {
        return $this->setData(self::PDP_DESCRIPTION, $text);
    }

    /**
     * @return string
     */
    public function getUrlPromotion()
    {
        return $this->getData(self::URL_PROMOTION);
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrlPromotion($url)
    {
        return $this->setData(self::URL_PROMOTION, $url);
    }
}
