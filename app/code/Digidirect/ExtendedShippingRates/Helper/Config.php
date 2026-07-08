<?php

namespace Digidirect\ExtendedShippingRates\Helper;

use Digidirect\Utilities\Model\System\Config\Backend\DefaultOptionModel;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 *
 * @package Digidirect\ExtendedShippingRates\Helper
 */
class Config extends AbstractHelper
{
    const ESR = 'digidirect_extendedshippingrates';

    const XML_PATH_HIDED_METHODS_RELATIONS = self::ESR . '/hide_methods/hided_methods_relations';
    const XML_PATH_MULTIPLE_RATES_PRICE = self::ESR . '/main/multiple_rates_price';

    const COUNTRY_COLUMN = 'country_column';
    const STATE_COLUMN = 'state_column';

    /**
     * @var DefaultOptionModel
     */
    protected $defaultOptionModel;

    /**
     * @var array
     */
    protected $hidedMethodsArr;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param DefaultOptionModel $defaultOptionModel
     */
    public function __construct(
        Context $context,
        DefaultOptionModel $defaultOptionModel
    ) {
        parent::__construct($context);

        $this->defaultOptionModel = $defaultOptionModel;
    }

    /**
     * @param null $scopeCode
     * @return array
     */
    public function getHidedMethods($scopeCode = null)
    {
        if (null === $this->hidedMethodsArr) {
            $hidedMethods = $this->scopeConfig->getValue(
                self::XML_PATH_HIDED_METHODS_RELATIONS,
                ScopeInterface::SCOPE_STORE,
                $scopeCode
            );

            $this->hidedMethodsArr = [];
            if (!empty($hidedMethods)) {
                $hidedMethodsRows = $this->defaultOptionModel->convertValueToArray($hidedMethods);
                foreach ($hidedMethodsRows as $hidedMethodsRow) {
                    $this->hidedMethodsArr[$hidedMethodsRow[self::COUNTRY_COLUMN]] =
                        $hidedMethodsRow[self::STATE_COLUMN];
                }
            }
        }

        return $this->hidedMethodsArr;
    }

    /**
     * @param null $scopeCode
     * @return int
     */
    public function getMultipleRatesPrice($scopeCode = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_MULTIPLE_RATES_PRICE,
            ScopeInterface::SCOPE_WEBSITE,
            $scopeCode
        );
    }
}
