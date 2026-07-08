<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2023 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Block\Popup\Fields;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Options;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Framework\View\Element\Template\Context;
use Plumrocket\Newsletterpopup\Model\Popup\GetFields;

/**
 * @since 4.6.0
 */
class CountryId extends Field
{

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\GetFields
     */
    private $getFields;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $directoryHelper;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    private $countryCollectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context                 $context
     * @param \Magento\Customer\Helper\Address                                 $addressHelper
     * @param \Magento\Customer\Api\CustomerMetadataInterface                  $customerMetadata
     * @param \Magento\Customer\Model\AttributeMetadataDataProvider            $attributeMetadataDataProvider
     * @param \Magento\Customer\Model\Options                                  $customerOptions
     * @param \Magento\Customer\Model\Customer                                 $customer
     * @param \Plumrocket\Newsletterpopup\Model\Popup\GetFields                $getFields
     * @param \Magento\Directory\Helper\Data                                   $directoryHelper
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param array                                                            $data
     */
    public function __construct(
        Context $context,
        Address $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        Options $customerOptions,
        Customer $customer,
        GetFields $getFields,
        Data $directoryHelper,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $addressHelper,
            $customerMetadata,
            $attributeMetadataDataProvider,
            $customerOptions,
            $customer,
            $data
        );
        $this->getFields = $getFields;
        $this->directoryHelper = $directoryHelper;
        $this->countryCollectionFactory = $countryCollectionFactory;
    }

    /**
     * Check if region field is enabled for popup.
     *
     * @param int $popupId
     * @return bool
     */
    public function isRegionFieldEnabled(int $popupId): bool
    {
        foreach ($this->getFields->onlyEnabled($popupId) as $popupField) {
            if ('region' === $popupField->getName()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get list of top countries.
     *
     * @return array
     */
    public function getTopCountryCodes(): array
    {
        return $this->directoryHelper->getTopCountryCodes();
    }

    /**
     * Returns country id.
     *
     * @return string
     */
    public function getCountryCode(): string
    {
        return (string) $this->directoryHelper->getDefaultCountry();
    }

    /**
     * Returns country collection instance
     *
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    public function getCountryCollection(): Collection
    {
        $collection = $this->getData('country_collection');
        if ($collection === null) {
            $collection = $this->countryCollectionFactory->create()->loadByStore();
            $this->setData('country_collection', $collection);
        }
        return $collection;
    }
}
