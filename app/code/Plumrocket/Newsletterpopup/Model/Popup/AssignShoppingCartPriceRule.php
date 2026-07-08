<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\Popup;

use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\ResourceModel\Rule;
use Magento\SalesRule\Model\RuleFactory;
use Plumrocket\Newsletterpopup\Api\Data\PopupInterface;

/**
 * @since 4.0.0
 */
class AssignShoppingCartPriceRule
{
    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    private $couponFactory;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule
     */
    private $ruleResource;

    /**
     * @param \Magento\SalesRule\Model\CouponFactory      $couponFactory
     * @param \Magento\SalesRule\Model\RuleFactory        $ruleFactory
     * @param \Magento\SalesRule\Model\ResourceModel\Rule $ruleResource
     */
    public function __construct(
        CouponFactory $couponFactory,
        RuleFactory $ruleFactory,
        Rule $ruleResource
    ) {
        $this->couponFactory = $couponFactory;
        $this->ruleFactory = $ruleFactory;
        $this->ruleResource = $ruleResource;
    }

    /**
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupInterface $popup
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupInterface
     */
    public function execute(PopupInterface $popup): PopupInterface
    {
        $ruleId = (int) $popup->getCouponCode();
        if ($ruleId) {
            /** @var \Magento\SalesRule\Model\Rule $rule */
            $rule = $this->ruleFactory->create();
            /** @var \Magento\SalesRule\Model\Coupon $coupon */
            $coupon = $this->couponFactory->create();

            $this->ruleResource->load($rule, $ruleId);
            if ($rule->getId()) {
                // If not auto generation, we need to load coupon from rule
                if (! $rule->getUseAutoGeneration()) {
                    $rule->setCoupon($coupon->loadPrimaryByRule($rule));
                }
                $popup->setCoupon($rule);
            }
        }

        return $popup;
    }
}
