<?php

namespace Digidirect\ExtendedShippingRates\Plugin\Magento\Quote\Model;

use Digidirect\ExtendedShippingRates\Model\Rule\Condition\DiscountCode;
use Digidirect\ExtendedShippingRates\Model\RuleAppliersAggregator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Quote\Model\CouponManagement;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Class CouponManagementPlugin
 */
class CouponManagementPlugin
{
    const RETURN_SET_CODE = 'SETCOUPONECODE';
    const RETURN_REMOVE_CODE = 'REMOVECOUPONECODE';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var RuleAppliersAggregator
     */
    protected $ruleAppliersAggregator;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * CouponManagementPlugin constructor.
     *
     * @param Registry $registry
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        Registry $registry,
        JsonHelper $jsonHelper = null
    ) {
        $this->registry = $registry;
        $this->jsonHelper = $jsonHelper ?: ObjectManager::getInstance()->get(JsonHelper::class);
    }

    /**
     * @param CouponManagement $object
     * @param mixed $result
     * @return mixed
     * @throws LocalizedException
     */
    public function afterSet(CouponManagement $object, $result)
    {
        if ($this->registry->registry(DiscountCode::ATTRIBUTE_CODE) == true) {
            throw new LocalizedException(__(self::RETURN_SET_CODE));
        }

        return $result;
    }

    /**
     * @param CouponManagement $object
     * @param mixed $result
     * @return mixed
     * @throws LocalizedException
     */
    public function afterRemove(CouponManagement $object, $result)
    {
        if ($result == true) {
            $result = $this->jsonHelper->jsonEncode(['message' => self::RETURN_REMOVE_CODE]);
        }

        return $result;
    }
}
