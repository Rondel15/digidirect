<?php

namespace Digidirect\ExtendedCartPriceRules\Model\ResourceModel;

use Digidirect\ExtendedCartPriceRules\Model\Rule\Action\Discount\RemoveCartItem;
use Digidirect\ExtendedCartPriceRules\Model\Rule\Action\Discount\RestrictAddToCart;
use Digidirect\ExtendedCartPriceRules\Model\Rule\Action\Discount\RestrictCheckout;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Logger\Monolog as Logger;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Model\RuleRepository;

/**
 * Class ExtendedCartPriceRule
 *
 * @package Digidirect\ExtendedCartPriceRules\Model\ResourceModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExtendedCartPriceRule extends AbstractDb
{
    /**
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    /**
     * @var bool
     */
    protected $_useIsObjectNew = true;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var RuleRepository
     */
    protected $ruleRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var array
     */
    protected $itemsAppliedToRuleAction;

    /**
     * @var array
     */
    protected $rules;

    /**
     * @var string
     */
    protected $_idFieldName = 'rule_id';

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * ExtendedCartPriceRule constructor.
     *
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param RuleRepository $ruleRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Logger $logger
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        RuleRepository $ruleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Logger $logger,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->checkoutSession = $checkoutSession;
        $this->ruleRepository = $ruleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('digidirect_extended_cart_price_rule', 'rule_id');
    }

    /**
     * @param string $action
     * @return array
     */
    public function getQuoteItemsAppliedToRuleAction($action)
    {
        if (isset($this->itemsAppliedToRuleAction[$action])) {
            return $this->itemsAppliedToRuleAction[$action];
        }

        $quote = $this->checkoutSession->getQuote();
        $quoteItems = $quote->getAllVisibleItems();
        if (!$quoteItems) {
            return [];
        }

        $itemsAppliedToRuleAction = [];
        foreach ($quoteItems as $item) {
            if (!$item->getIsRemoveItemRuleApplied()) {
                continue;
            }
            $itemsAppliedToRuleAction[$item->getId()] = $item;
        }

        $this->itemsAppliedToRuleAction[$action] = $itemsAppliedToRuleAction;

        return $itemsAppliedToRuleAction;
    }

    /**
     * @param string $action
     * @return \Magento\SalesRule\Api\Data\RuleInterface[]
     */
    public function getRulesByAction($action)
    {
        if (isset($this->rules[$action])) {
            return $this->rules[$action];
        }
        $criteria = $this->searchCriteriaBuilder
            ->addFilter('simple_action', $action)
            ->create();
        $rules = $this->ruleRepository->getList($criteria)->getItems();
        foreach ($rules as $key => $rule) {
            $rules[$rule->getRuleId()] = $rule;
            unset($rules[$key]);
        }

        return $this->rules[$action] = $rules;
    }

    /**
     * @param Quote $quote
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function addRemoveItemRuleFlagToQuote(Quote $quote)
    {
        $removeCartItemRules = $this->getRulesByAction(RemoveCartItem::SIMPLE_ACTION);
        if (empty($removeCartItemRules)) {
            return;
        }
        $quoteAppliedRuleIds = [];
        if ($quote->getId()) {
            $ruleIds = $quote->getAppliedRuleIds() ?: '';
            $appliedRuleIds = explode(',', $ruleIds);
            $quoteAppliedRuleIds = array_flip($appliedRuleIds);
        }

        try {
            foreach ($quote->getAllVisibleItems() as $quoteItem) {
                $quoteItem->setIsRemoveItemRuleApplied(false);
                $quoteItem->setData(RemoveCartItem::EXT_ATTR_INTERNAL_ALIAS, false);
                $ruleIds = $quoteItem->getAppliedRuleIds() ?: '';
                if (!$ruleIds) {
                    continue;
                }
                $appliedRuleIds = array_flip(explode(',', $ruleIds));
                foreach ($removeCartItemRules as $ruleId => $rule) {
                    $isRemoveItemRuleApplied = isset($appliedRuleIds[$ruleId]) && isset($quoteAppliedRuleIds[$ruleId]);
                    $quoteItem->setIsRemoveItemRuleApplied($isRemoveItemRuleApplied);
                    $quoteItem->setData(RemoveCartItem::EXT_ATTR_INTERNAL_ALIAS, $isRemoveItemRuleApplied);
                }
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->warning($e->getMessage(), $quote->toArray());
        }
    }

    /**
     * @param Quote $quote
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRestrictCheckoutErrorMessages(Quote $quote)
    {
        return $this->getRestrictionRulesByQuote($quote, RestrictCheckout::SIMPLE_ACTION);
    }

    /**
     * @param Quote $quote
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRestrictAddToCartErrorMessages(Quote $quote)
    {
        return $this->getRestrictionRulesByQuote($quote, RestrictAddToCart::SIMPLE_ACTION);
    }

    /**
     * @param Quote $quote
     * @param string $ruleActionCode
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRestrictionRulesByQuote(Quote $quote, $ruleActionCode)
    {
        $appliedRuleIds = $quote->getAppliedRuleIds();
        if (!$appliedRuleIds) {
            return [];
        }

        $appliedRuleIds = explode(',', $appliedRuleIds);

        $adapter = $this->getConnection();
        $select = $adapter->select();
        $select->distinct(true)
            ->from(['rule' => $this->getTable('salesrule')], ['rule_id'])
            ->join(
                ['ext_rule' => $this->getMainTable()],
                'rule.rule_id = ext_rule.rule_id',
                ['message']
            )
            ->where('rule.simple_action = ?', $ruleActionCode)
            ->where('rule.rule_id IN (?)', $appliedRuleIds);

        return $adapter->fetchPairs($select);
    }

    /**
     * @param int $id
     * @return \Magento\SalesRule\Api\Data\RuleInterface|\Magento\SalesRule\Model\Data\Rule
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRuleById($id)
    {
        return $this->ruleRepository->getById($id);
    }

    /**
     * @param array $ids
     * @return \Magento\SalesRule\Api\Data\RuleSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRulesById(array $ids)
    {
        $criteria = $this->searchCriteriaBuilder
            ->addFilter('rule_id', $ids, 'in')
            ->create();

        return $this->ruleRepository->getList($criteria);
    }
}
