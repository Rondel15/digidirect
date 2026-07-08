<?php
namespace Digidirect\ExtendedCartPriceRules\Model\Rule\Action;

use Magento\SalesRule\Model\Validator;
use Magento\Quote\Api\Data\CartInterface;

class PaymentMethodLimit extends Validator
{
    /**
     * If the method returns NULL then no payment restrictions exist
     *
     * @param CartInterface $quote
     * @return array|null
     */
    public function getAvailableMethods(CartInterface $quote)
    {
        $hasLimit = false;
        $availableMethods = [];
        $items = $quote->getItems() ?: [];
        foreach ($items as $item) {
            $address = $item->getAddress();
            foreach ($this->_getRules($address) as $rule) {
                if (!$this->validatorUtility->canProcessRule($rule, $address)) {
                    continue;
                }

                if (!$rule->getActions()->validate($item)) {
                    continue;
                }

                $paymentMethodLimit = array_filter(explode(',', $rule->getPaymentMethodLimit()));
                if (!empty($paymentMethodLimit)) {
                    $hasLimit = true;
                    if (empty($availableMethods)) {
                        $availableMethods = $paymentMethodLimit;
                    } else {
                        $availableMethods = array_intersect($availableMethods, $paymentMethodLimit);
                    }
                }
            }
        }

        if (false === $hasLimit) {
            return null;
        }

        return $availableMethods;
    }

    /**
     * Get extend rule data
     *
     * @param CartInterface $quote
     * @return array
     */
    public function getExtendRulesData(CartInterface $quote)
    {
        $result = [];
        $items = $quote->getItems() ?: [];
        $defaultMessage = __('Selected Payment Method Is Not Available For Your Order');

        foreach ($items as $item) {
            $address = $item->getAddress();
            foreach ($this->_getRules($address) as $rule) {
                if (!$this->validatorUtility->canProcessRule($rule, $address)) {
                    continue;
                }

                if (!$rule->getActions()->validate($item)) {
                    continue;
                }

                $isEnableFlag = (bool) $rule->getEnableUnavailablePaymentMethods() ?? false;
                $message = $rule->getMessageForUnavailablePaymentMethod() ?? $defaultMessage;

                if (is_string($message)) {
                    $message = __($message);
                }

                $result[$rule->getId()] = [
                    'isEnableUnavailablePaymentMethods' => $isEnableFlag,
                    'messageForUnavailablePaymentMethod' => $message
                ];
            }
        }

        return $result;
    }
}
