<?php
namespace Digidirect\CollectAbstractEntity\Helper;

use Digidirect\Collect\Model\OrderStoreLocatorInfo;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 *
 * @package Digidirect\CollectAbstractEntity\Helper
 */
class Config extends AbstractHelper
{
    const XML_PATH_COLLECT_FIELDS_MATRIX = 'carriers/collect/click_collect_fields_matrix';
    const XML_PATH_COLLECT_ABSTRACT_ENTITY = 'carriers/collect/click_collect_entity';
    const XML_PATH_PREFILL_SHIPPING_ADDRESS = 'carriers/collect/prefill_shipping_address_fields_matrix';

    const XML_ENABLE_TRACKING_NUMBER_ADDING_NOTIFICATION =
        'carriers/collect/enable_tracking_number_adding_notification';
    const XML_STORE_EMAIL_ATTRIBUTE = 'carriers/collect/store_email_attribute';
    const XML_TRACKING_NUMBER_ADDING_NOTIFICATION_TEMPLATE =
        'carriers/collect/tracking_number_adding_notification_template';

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var array
     */
    protected $collectFields;

    /**
     * @var string[]
     */
    protected $prefillShippingAddressFields;

    /**
     * @var OrderStoreLocatorInfo
     */
    protected $orderStoreLocatorInfo;

    /**
     * Config constructor.
     *
     * @param Context $context
     * @param OrderStoreLocatorInfo $orderStoreLocatorInfo
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        OrderStoreLocatorInfo $orderStoreLocatorInfo,
        SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->orderStoreLocatorInfo = $orderStoreLocatorInfo;
        $this->serializer = $serializer;
    }

    /**
     * @param null|string $storeId
     * @return string
     */
    public function getCollectAbstractEntityId($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COLLECT_ABSTRACT_ENTITY,
            ScopeInterface::SCOPE_WEBSITE,
            $storeId
        );
    }

    /**
     * @param null|string $storeId
     * @return array
     */
    public function getCollectFieldsMatrix($storeId = null)
    {
        if (null === $this->collectFields) {
            $json = $this->scopeConfig->getValue(
                self::XML_PATH_COLLECT_FIELDS_MATRIX,
                ScopeInterface::SCOPE_WEBSITE,
                $storeId
            );
            $array = $json ? $this->serializer->unserialize($json) : [];

            $this->collectFields = [];
            foreach ($array as $item) {
                $this->collectFields[$item['collect_field_column']] = $item['abstract_entity_attribute_field_column'];
            }
        }

        return $this->collectFields;
    }

    /**
     * @param null|string $storeId
     * @return array
     */
    public function getPrefillShippingAddressFieldsMatrix($storeId = null)
    {
        if (null === $this->prefillShippingAddressFields) {
            $json = $this->scopeConfig->getValue(
                self::XML_PATH_PREFILL_SHIPPING_ADDRESS,
                ScopeInterface::SCOPE_WEBSITE,
                $storeId
            );
            $array = $json ? $this->serializer->unserialize($json) : [];

            $this->prefillShippingAddressFields = [];
            foreach ($array as $item) {
                $this->prefillShippingAddressFields[$item['shipping_address_field_column']] =
                    $item['abstract_entity_attribute_field_column'];
            }

        }

        return $this->prefillShippingAddressFields;
    }

    /**
     * @return bool
     */
    public function isTrackingNumberAddingNotificationEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_ENABLE_TRACKING_NUMBER_ADDING_NOTIFICATION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getStoreEmailAttribute()
    {
        return $this->scopeConfig->getValue(self::XML_STORE_EMAIL_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed|null
     */
    public function getStoreEmailAttributeIfEnabled()
    {
        return $this->isTrackingNumberAddingNotificationEnabled() ? $this->getStoreEmailAttribute() : null;
    }

    /**
     * @return mixed
     */
    public function getTrackingNumberAddingNotificationTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_TRACKING_NUMBER_ADDING_NOTIFICATION_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed|null
     */
    public function getTrackingNumberAddingNotificationTemplateIfEnabled()
    {
        return $this->isTrackingNumberAddingNotificationEnabled() ?
            $this->getTrackingNumberAddingNotificationTemplate() : null;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return null
     * @throws \Exception
     */
    public function getEmailForTrackingNumberAddingNotification(\Magento\Sales\Model\Order $order)
    {
        $storeEmailAttribute = $this->getStoreEmailAttributeIfEnabled();
        if (empty($storeEmailAttribute) || !is_string($storeEmailAttribute)) {
            return null;
        }
        $storeLocatorItem = $this->orderStoreLocatorInfo->getStoreLocatorItemByOrder($order);

        return $storeLocatorItem->getData($storeEmailAttribute);
    }
}

//Digidirect\CollectAbstractEntityMsi\Plugin\Digidirect\CollectAbstractEntity\Model\CollectPlaceRepositoryPlugin