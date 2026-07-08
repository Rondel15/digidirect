<?php
namespace Digidirect\AbstractAttributes\Api\Data;

/**
 * AbstractAttribute interface.
 * @api
 */
interface AbstractAttributeInterface
{
    /**#@-
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = 'row_id';
    const STORE_ID = 'store_id';
    const ATTRIBUTE_ID = 'attribute_id';
    const ATTRIBUTE_CODE = 'attribute_code';
    const ATTRIBUTED_LABEL = 'frontend_label';
    const STATUS = 'status';
    const LISTING_ENABLED = 'listing_enabled';
    const URL_KEY = 'url_key';
    const META_TITLE = 'meta_title';
    const META_DESC = 'meta_desc';
    const PAGE_TEMPLATE = 'page_template';
    const CUSTOM_TEMPLATE = 'custom_template';
    /**#@-*/

    const EAV_FIELDS = [
        self::ATTRIBUTE_CODE,
        self::ATTRIBUTED_LABEL
    ];

    /**
     * Get id
     * @return int|null
     */
    public function getId();

    /**
     * Set id
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get store id
     * @return int|null
     */
    public function getStoreId();

    /**
     * Set store id
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get attribute id
     * @return int|null
     */
    public function getAttributeId();

    /**
     * Set attribute id
     * @param int $id
     * @return $this
     */
    public function setAttributeId($id);

    /**
     * Get attribute code
     * @return int|null
     */
    public function getAttributeCode();

    /**
     * Set attribute code
     * @param string $code
     * @return $this
     */
    public function setAttributeCode($code);

    /**
     * Get attribute label
     * @return int|null
     */
    public function getAttributeLabel();

    /**
     * Set attribute label
     * @param string $label
     * @return $this
     */
    public function setAttributeLabel($label);

    /**
     * Get status
     * @return int|null
     */
    public function getStatus();

    /**
     * Set status
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get listing enabled
     * @return int|null
     */
    public function getListingEnabled();

    /**
     * Set listing enabled
     * @param int $listingEnabled
     * @return $this
     */
    public function setListingEnabled($listingEnabled);

    /**
     * Get url key
     * @return string|null
     */
    public function getUrlKey();

    /**
     * Set url key
     * @param string $urlKey
     * @return $this
     */
    public function setUrlKey($urlKey);

    /**
     * Get direct url
     * @return string
     */
    public function getUrl();

    /**
     * Get meta title
     * @return string|null
     */
    public function getMetaTitle();

    /**
     * Set meta title
     * @param string $metaTitle
     * @return $this
     */
    public function setMetaTitle($metaTitle);

    /**
     * Get meta description
     * @return string|null
     */
    public function getMetaDesc();

    /**
     * Set meta description
     * @param string $metaDesc
     * @return $this
     */
    public function setMetaDesc($metaDesc);

    /**
     * Get page template
     * @return string|null
     */
    public function getPageTemplate();

    /**
     * Set meta description
     * @param string $template
     * @return $this
     */
    public function setPageTemplate($template);

    /**
     * Get page template
     * @return string|null
     */
    public function getCustomTemplate();

    /**
     * Set meta description
     * @param string $template
     * @return $this
     */
    public function setCustomTemplate($template);
}
