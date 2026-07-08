<?php

namespace Digidirect\ExtendedCartPriceRules\Observer;

use Digidirect\ExtendedCartPriceRules\Api\ExtendedCartPriceRuleRepositoryInterface;
use Digidirect\ExtendedCartPriceRules\Model\ExtendedCartPriceRule;
use Digidirect\ExtendedCartPriceRules\Model\ExtendedCartPriceRuleFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class SalesRuleSaveAfter implements ObserverInterface
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
     * SalesruleRuleSaveAfter constructor.
     *
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
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $entity = $observer->getEntity();
        if (!$entity || !$entity->getRuleId() || !$entity->getActionMessage()) {
            return;
        }

        try {
            $extendedCartPriceRule = $this->cartPriceRuleRepository->get($entity->getRuleId());
        } catch (NoSuchEntityException $exception) {
            $extendedCartPriceRule = $this->extendedCartPriceRuleFactory->create();
            $extendedCartPriceRule->setRuleId($entity->getRuleId());
            $extendedCartPriceRule->isObjectNew(true);
        }
        $extendedCartPriceRule->setMessage($entity->getActionMessage());
        $this->cartPriceRuleRepository->save($extendedCartPriceRule);
    }
}
