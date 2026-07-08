<?php

namespace Digidirect\ExtendedCartPriceRules\Plugin\Magento\SalesRule\ResourceModel\Rule;

use Digidirect\ExtendedCartPriceRules\Api\ExtendedCartPriceRuleRepositoryInterface;
use Digidirect\ExtendedCartPriceRules\Model\ExtendedCartPriceRuleFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection as Subject;

class Collection
{
    /**
     * @var ExtendedCartPriceRuleRepositoryInterface
     */
    protected $cartPriceRuleRepository;

    /**
     * @var ExtendedCartPriceRuleFactory
     */
    protected $extendedCartPriceRuleFactory;

    /**
     * @param ExtendedCartPriceRuleRepositoryInterface $cartPriceRuleRepository
     * @param ExtendedCartPriceRuleFactory $extendedCartPriceRuleFactory
     */
    public function __construct(
        ExtendedCartPriceRuleRepositoryInterface $cartPriceRuleRepository,
        ExtendedCartPriceRuleFactory $extendedCartPriceRuleFactory
    ) {
        $this->cartPriceRuleRepository = $cartPriceRuleRepository;
        $this->extendedCartPriceRuleFactory = $extendedCartPriceRuleFactory;
    }

    /**
     * @param Subject $subject
     * @param object $item
     * @return array|void
     */
    public function beforeAddItem(Subject $subject, $item)
    {
        try {
            $extendedCartPriceRule = $this->cartPriceRuleRepository->get($item->getRuleId());
        } catch (NoSuchEntityException $exception) {
            return;
        }

        $item->setActionMessage($extendedCartPriceRule->getMessage());

        return [$item];
    }
}
