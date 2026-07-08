<?php

namespace Digidirect\CheckoutFields\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface OrderFieldValueInterface
 *
 * @package Digidirect\CheckoutFields\Api\Data
 */
interface OrderFieldValueInterface extends ExtensibleDataInterface
{
    /**
     * Entity ID
     */
    const ENTITY_ID = 'entity_id';

    /**
     * Code
     */
    const CODE = 'code';

    /**
     * Order ID
     */
    const ORDER_ID = 'order_id';

    /**
     * Value
     */
    const VALUE = 'value';

    /**
     *  Field Id
     */
    const FIELD_ID = 'field_id';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int|null
     */
    public function getOrderId();

    /**
     * @return string|null
     */
    public function getValue();

    /**
     * @return string|null
     */
    public function getCode();

    /**
     * @return string|null
     */
    public function getFieldId();

    /**
     * @param string $code
     * @return OrderFieldValueInterface
     */
    public function setCode($code);

    /**
     * @param int $id
     * @return OrderFieldValueInterface
     */
    public function setId($id);

    /**
     * @param int $orderId
     * @return OrderFieldValueInterface
     */
    public function setOrderId($orderId);

    /**
     * @param string|array $value
     * @return OrderFieldValueInterface
     */
    public function setValue($value);

    /**
     * @param string $fieldId
     * @return OrderFieldValueInterface
     */
    public function setFieldId($fieldId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Digidirect\CheckoutFields\Api\Data\OrderFieldValueExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Digidirect\CheckoutFields\Api\Data\OrderFieldValueExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Digidirect\CheckoutFields\Api\Data\OrderFieldValueExtensionInterface $extensionAttributes
    );
}
