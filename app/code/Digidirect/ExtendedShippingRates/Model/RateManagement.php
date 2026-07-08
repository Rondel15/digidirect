<?php
namespace Digidirect\ExtendedShippingRates\Model;

use Digidirect\ExtendedShippingRates\Helper\Config;

/**
 * Class RateManagement
 *
 * @package Digidirect\ExtendedShippingRates\Model
 */
class RateManagement implements \Digidirect\ExtendedShippingRates\Api\RateManagementInterface
{
    /**
     * @var \Digidirect\ExtendedShippingRates\Helper\Config
     */
    protected $configHelper;

    /**
     * GetAllRates constructor.
     *
     * @param \Digidirect\ExtendedShippingRates\Helper\Config $configHelper
     */
    public function __construct(Config $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function removeDisabledMethods($shippingAddress)
    {
        $disabledShippingMethods = $this->getDisabledShippingMethods($shippingAddress);
        if (!$disabledShippingMethods) {
            return $this;
        }

        /** @var array of \Magento\Quote\Model\Quote\Address\Rate\Interceptor $rates */
        $rates = $shippingAddress->getAllShippingRates();
        /** @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $ratesCollection */
        $ratesCollection = $shippingAddress->getShippingRatesCollection();

        $rateKeys = [];
        foreach ($ratesCollection as $key => $item) {
            $rateKeys[$item->getCode()] = $key;
        }

        /** @var \Magento\Quote\Model\Quote\Address\Rate $rate */
        foreach ($rates as $rate) {
            $code = $rate->getCode();
            if (!isset($disabledShippingMethods[$code])) {
                continue;
            }

            if (!$disabledShippingMethods[$code]) {
                $rateKey = $rate->getId() ?: $rateKeys[$code] ?? null;
                $ratesCollection->removeItemByKey($rateKey);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisabledShippingMethods($shippingAddress)
    {
        /** @var array $disabledShippingMethods */
        $disabledShippingMethods = (array)$shippingAddress->getDisabledShippingMethods();

//        $hidedMethods = $this->configHelper->getHidedMethods();
//        if ($hidedMethods) {
//            /** @var array of \Magento\Quote\Model\Quote\Address\Rate\Interceptor $rates */
//            $rates = $shippingAddress->getAllShippingRates();
//
//            $disabledShippingMethods = $this->applyHiddenMethods(
//                $disabledShippingMethods,
//                $hidedMethods,
//                $rates
//            );
//        }

        return $disabledShippingMethods;
    }

    /**
     * Add hidden methods to disabled array
     *
     * @param array $disabledShippingMethods
     * @param array $hidedMethods
     * @param array $rates
     * @return $this
     */
    public function applyHiddenMethods(array $disabledShippingMethods, array $hidedMethods, array $rates)
    {
        foreach ($rates as $rate) {
            $code = $rate->getCode();
            if (isset($hidedMethods[$code])) {
                if ($hidedMethods[$code]) {
                    $disabledShippingMethods = array_merge(
                        $disabledShippingMethods,
                        array_combine($hidedMethods[$code], array_fill(0, count($hidedMethods[$code]), ''))
                    );
                }

                unset($hidedMethods[$code]);

                if (!$hidedMethods) {
                    break;
                }
            }
        }

        return $disabledShippingMethods;
    }
}
