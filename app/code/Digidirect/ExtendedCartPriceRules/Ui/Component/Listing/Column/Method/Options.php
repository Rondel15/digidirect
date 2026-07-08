<?php

namespace Digidirect\ExtendedCartPriceRules\Ui\Component\Listing\Column\Method;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Config;
use Magento\Store\Model\Store;

/**
 * Class Options
 *
 * @package Digidirect\ExtendedCartPriceRules\Ui\Component\Listing\Column\Method
 */
class Options implements OptionSourceInterface
{
    const SORTED_LIST_ITEMS = true;
    const UNSORTED_LIST_ITEMS = false;

    const LABEL_LIST_ITEMS = true;
    const NON_LABEL_LIST_ITEMS = false;

    const WITH_GROUPS_LIST_ITEMS = true;
    const WITHOUT_GROUPS_LIST_ITEMS = false;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var Data
     */
    protected $paymentHelper;

    /**
     * @var Config
     */
    protected $paymentConfig;

    /**
     * Constructor
     *
     * @param Data $paymentHelper
     * @param \Magento\Payment\Model\Config $paymentConfig
     */
    public function __construct(
        Data $paymentHelper,
        Config $paymentConfig
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->paymentConfig = $paymentConfig;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     * @throws LocalizedException
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = $this->getPaymentMethodList(self::SORTED_LIST_ITEMS, self::LABEL_LIST_ITEMS);
        }

        return $this->options;
    }

    /**
     * Retrieve all payment methods list as an array
     * Possible output:
     * 1) assoc array as <code> => <title>
     * 2) array of array('label' => <title>, 'value' => <code>)
     *
     * @param bool $sorted
     * @param bool $asLabelValue
     * @param bool $withGroups
     * @param Store|null $store
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function getPaymentMethodList(
        $sorted = self::SORTED_LIST_ITEMS,
        $asLabelValue = self::NON_LABEL_LIST_ITEMS,
        $withGroups = self::WITHOUT_GROUPS_LIST_ITEMS,
        $store = null
    ) {
        $methods = [];
        $groups = [];
        $groupRelations = [];

        foreach ($this->paymentHelper->getPaymentMethods() as $code => $data) {
            $methods[$code] = $this->getMethodTitle($code, $data, $store);

            if ($asLabelValue && $withGroups && isset($data['group'])) {
                $groupRelations[$code] = $data['group'];
            }
        }
        if ($asLabelValue && $withGroups) {
            $groups = $this->paymentConfig->getGroups();
            foreach ($groups as $code => $title) {
                $methods[$code] = $title;
            }
        }
        if ($sorted) {
            asort($methods);
        }
        if ($asLabelValue) {
            $labelValues = [];
            foreach ($methods as $code => $title) {
                $labelValues[$code] = [];
            }
            foreach ($methods as $code => $title) {
                if (isset($groups[$code])) {
                    $labelValues[$code]['label'] = $title;
                    if (!isset($labelValues[$code]['value'])) {
                        $labelValues[$code]['value'] = null;
                    }
                } elseif (isset($groupRelations[$code])) {
                    unset($labelValues[$code]);
                    $labelValues[$groupRelations[$code]]['value'][$code] = ['value' => $code, 'label' => $title];
                } else {
                    $labelValues[$code] = ['value' => $code, 'label' => $title];
                }
            }

            return $labelValues;
        }

        return $methods;
    }

    /**
     * Excluded filtering by status
     *
     * @param string $methodCode
     * @param array $methodConfigData
     * @param null $store
     * @return null|string
     * @throws LocalizedException
     */
    protected function getMethodTitle(string $methodCode, array $methodConfigData, $store = null)
    {
        $storedTitle = $this->paymentHelper->getMethodInstance($methodCode)->getConfigData('title', $store);
        if (isset($storedTitle)) {
            return $storedTitle;
        }
        if (isset($data['title'])) {
            return $methodConfigData['title'];
        }

        return null;
    }
}
