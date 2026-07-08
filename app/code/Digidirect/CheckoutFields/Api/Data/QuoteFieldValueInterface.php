<?php

namespace Digidirect\CheckoutFields\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface QuoteFieldValueInterface
 * @package Digidirect\CheckoutFields\Api\Data
 */
interface QuoteFieldValueInterface extends ExtensibleDataInterface
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
     * Quote ID
     */
    const QUOTE_ID = 'quote_id';

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
    public function getQuoteId();

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
     * @return QuoteFieldValueInterface
     */
    public function setCode($code);

    /**
     * @param int $id
     * @return QuoteFieldValueInterface
     */
    public function setId($id);

    /**
     * @param int $quoteId
     * @return QuoteFieldValueInterface
     */
    public function setQuoteId($quoteId);

    /**
     * @param string|array $value
     * @return QuoteFieldValueInterface
     */
    public function setValue($value);

    /**
     * @param string $fieldId
     * @return QuoteFieldValueInterface
     */
    public function setFieldId($fieldId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Digidirect\CheckoutFields\Api\Data\QuoteFieldValueExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Digidirect\CheckoutFields\Api\Data\QuoteFieldValueExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Digidirect\CheckoutFields\Api\Data\QuoteFieldValueExtensionInterface $extensionAttributes
    );
}
