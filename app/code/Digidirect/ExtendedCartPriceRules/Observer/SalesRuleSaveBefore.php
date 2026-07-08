<?php
namespace Digidirect\ExtendedCartPriceRules\Observer;

use \Digidirect\ExtendedCartPriceRules\Model\ResourceModel\ExtendedCartPriceRule;
use Digidirect\ExtendedCartPriceRules\Model\Rule\Action\Discount\RemoveCartItem;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class SalesRuleSaveBefore implements ObserverInterface
{
    /**
     * @var ExtendedCartPriceRule
     */
    protected $extendedCartPriceRule;

    /**
     * SalesRuleSaveBefore constructor.
     *
     * @param ExtendedCartPriceRule $extendedCartPriceRule
     */
    public function __construct(
        ExtendedCartPriceRule $extendedCartPriceRule
    ) {
        $this->extendedCartPriceRule = $extendedCartPriceRule;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $entity = $observer->getEntity();
        if (!$entity) {
            return;
        }

        if ($entity->getSimpleAction() === RemoveCartItem::SIMPLE_ACTION) {
            $rules = $this->extendedCartPriceRule->getRulesByAction(RemoveCartItem::SIMPLE_ACTION);
            foreach ($rules as $rule) {
                if (!$entity->getId() || ($entity->getId() !== $rule->getRuleId())) {
                    throw new LocalizedException(__('Only one rule can have "Remove Cart Item" action'));
                }
            }
        }
    }
}
