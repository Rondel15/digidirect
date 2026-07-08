<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\Config\Source\Popup;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleRepository;

/**
 * @since 1.0.0
 */
class Coupon implements OptionSourceInterface
{
    /**
     * @var \Magento\SalesRule\Model\RuleRepository
     */
    private $ruleRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param \Magento\SalesRule\Model\RuleRepository      $ruleRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        RuleRepository $ruleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get list of cart price rules.
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toOptionArray(): array
    {
        $options = [__('No')];

        $this->searchCriteriaBuilder
            ->addFilter('to_date', true, 'null')
            ->addFilter('use_auto_generation', true)
            ->addFilter('coupon_type', Rule::COUPON_TYPE_SPECIFIC);

        $rules = $this->ruleRepository->getList($this->searchCriteriaBuilder->create());

        foreach ($rules->getItems() as $rule) {
            $options[$rule->getRuleId()] = $rule->getName();
        }

        return $options;
    }
}
