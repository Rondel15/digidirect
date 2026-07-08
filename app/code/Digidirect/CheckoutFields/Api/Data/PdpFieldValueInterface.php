<?php

namespace Digidirect\CheckoutFields\Api\Data;

/**
 * Interface PdpFieldValueInterface
 * @package Digidirect\CheckoutFields\Api\Data
 */
interface PdpFieldValueInterface
{
    /**
     * Entity ID
     */
    const ENTITY_ID = 'entity_id';

    /**
     * Quote ID
     */
    const QUOTE_ID = 'quote_id';

    /**
     * Product ID
     */
    const PRODUCT_ID = 'product_id';

    /**
     * Field code
     */
    const FIELD_CODE = 'field_code';

    /**
     * Field value
     */
    const FIELD_VALUE = 'field_value';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getQuoteId();

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @return string
     */
    public function getFieldCode();

    /**
     * @return string
     */
    public function getFieldValue();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId);

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * @param string $code
     * @return $this
     */
    public function setFieldCode($code);

    /**
     * @param string $value
     * @return $this
     */
    public function setFieldValue($value);
}
