<?php
namespace Digidirect\ExtendedShippingRates\Model\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;

class AddMethods
{
    /**
     * @var \Digidirect\ExtendedShippingRates\Model\CarrierFactory
     */
    protected $carrierFactory;

    /**
     * @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Carrier\CollectionFactory
     */
    protected $carrierCollectionFactory;

    /**
     * @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Carrier\Collection
     */
    protected $carriersCollection;

    /**
     * @var array
     */
    protected $loadedCarriers = [];

    /**
     * @param \Digidirect\ExtendedShippingRates\Model\CarrierFactory $carrierFactory
     * @param \Digidirect\ExtendedShippingRates\Model\ResourceModel\Carrier\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Digidirect\ExtendedShippingRates\Model\CarrierFactory $carrierFactory,
        \Digidirect\ExtendedShippingRates\Model\ResourceModel\Carrier\CollectionFactory $collectionFactory
    ) {
        $this->carrierFactory = $carrierFactory;
        $this->carrierCollectionFactory = $collectionFactory;
    }

    /**
     * @param array $subject
     * @param string $proceed
     * @param null $path
     * @param string $scope
     * @param null $scopeCode
     * @return mixed|null
     */
    public function aroundGetValue(
        $subject,
        $proceed,
        $path = null,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        $returnValue = $proceed($path, $scope, $scopeCode);
        if (mb_stripos($path, 'carriers') === 0) {
            $pathParts = explode('/', $path);
            $partsCount = count($pathParts);

            // Do not process existing in the config value, because it has highest priority
            if ($partsCount > 1 && $returnValue !== null) {
                return $returnValue;
            }

            switch ($partsCount) {
                case 1:
                    $this->prepareCarriers();
                    $returnValue = $this->addCarriers($returnValue);
                    break;
                case 2:
                    $this->prepareCarriers();
                    $code = $pathParts[1];
                    $returnValue = $this->getSpecificCarrierData($code);
                    break;
                case 3:
                    $this->prepareCarriers();
                    $code = $pathParts[1];
                    $param = $pathParts[2];
                    $returnValue = $param == '' ? null : $this->getSpecificCarrierData($code, $param);
                    break;
            }
        }

        return $returnValue;
    }

    /**
     * Get all data of the carrier specified by code (carrier_code)
     * It's possible to get the specified parameter ($param) of the carrier
     *
     * @param string $code
     * @param null $param
     * @return mixed|null
     */
    protected function getSpecificCarrierData($code, $param = null)
    {
        $item = $this->carriersCollection->getItemByColumnValue('carrier_code', $code);
        if (!$item) {
            return null;
        }

        if (!$param) {
            return $item->getData();
        }

        return $item->getData($param);
    }

    /**
     * Add all available carriers to the result
     *
     * @param array $returnValue
     * @return mixed
     */
    protected function addCarriers($returnValue)
    {
        foreach ($this->loadedCarriers as $carrier) {
            $code = $carrier->getData('carrier_code');
            if (isset($returnValue[$code])) {
                continue;
            }

            $returnValue[$code] = $carrier->getData();
        }

        return $returnValue;
    }

    /**
     * Prepare carriers collection & load items
     *
     * @return void
     */
    protected function prepareCarriers()
    {
        if (empty($this->loadedCarriers)) {
            /** @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Carrier\Collection $carriersCollection */
            $this->carriersCollection = $this->carrierCollectionFactory->create();
            $this->loadedCarriers = $this->carriersCollection->getItems();
        }
    }
}
