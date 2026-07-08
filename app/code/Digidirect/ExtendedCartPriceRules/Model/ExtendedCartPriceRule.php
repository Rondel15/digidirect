<?php
namespace Digidirect\ExtendedCartPriceRules\Model;

use Digidirect\ExtendedCartPriceRules\Api\Data\ExtendedCartPriceRuleInterface;
use Magento\Framework\Model\AbstractModel;

class ExtendedCartPriceRule extends AbstractModel implements ExtendedCartPriceRuleInterface
{
    /**
     * Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Digidirect\ExtendedCartPriceRules\Model\ResourceModel\ExtendedCartPriceRule');
    }

    /**
     * @return int
     */
    public function getRuleId()
    {
        return $this->getData(static::RULE_ID);
    }

    /**
     * @param integer $id
     * @return static
     */
    public function setRuleId($id)
    {
        return $this->setData(static::RULE_ID, $id);
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->getData(static::MESSAGE);
    }

    /**
     * @param string $message
     * @return static
     */
    public function setMessage($message)
    {
        return $this->setData(static::MESSAGE, $message);
    }
}
