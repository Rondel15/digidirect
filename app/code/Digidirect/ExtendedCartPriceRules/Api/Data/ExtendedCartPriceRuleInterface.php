<?php
namespace Digidirect\ExtendedCartPriceRules\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface ExtendedCartPriceRuleTableInterface
 * @api
 */
interface ExtendedCartPriceRuleInterface extends ExtensibleDataInterface
{
    const RULE_ID = 'rule_id';
    const MESSAGE = 'message';

    /**
     * @return int
     */
    public function getRuleId();

    /**
     * @param integer $id
     * @return static
     */
    public function setRuleId($id);

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @param string $message
     * @return static
     */
    public function setMessage($message);
}
