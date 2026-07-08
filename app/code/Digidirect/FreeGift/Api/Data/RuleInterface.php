<?php

namespace Digidirect\FreeGift\Api\Data;

interface RuleInterface
{
    const RULE_TYPE_ALL = 0;
    const RULE_TYPE_ONE = 1;

    const FIELD_ID = 'entity_id';
    const FIELD_SALESRULE_ID = 'salesrule_id';
    const FIELD_TYPE = 'type';
    const FIELD_SKU = 'sku';
    const FIELD_ENABLE_ON_PDP = 'enable_on_pdp';
    const FIELD_SHOW_DESC_ON_PDP = 'show_desc_on_pdp';
    const FIELD_DESCRIPTION_LABEL = 'description_label';
    const FIELD_DESCRIPTION = 'description';
    const FIELD_IS_HIDDEN_FOR_CUSTOMER = 'is_hidden_for_customer';
    const FIELD_CART_MESSAGE = 'cart_message';
    const FIELD_PREFIX = 'prefix';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int
     */
    public function getType();

    /**
     * @return string
     */
    public function getSku();

    /**
     * @return array
     */
    public function getSkuArray();

    /**
     * @return int
     */
    public function isEnabledOnPdp();

    /**
     * @return int
     */
    public function getShowDescOnPdp();

    /**
     * @return string
     */
    public function getDescriptionLabel();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return int
     */
    public function isHiddenForCustomer();
}
