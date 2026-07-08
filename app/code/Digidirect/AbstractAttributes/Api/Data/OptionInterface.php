<?php

namespace Digidirect\AbstractAttributes\Api\Data;

/**
 * AbstractAttribute interface.
 *
 * @api
 */
interface OptionInterface
{
    /**#@-
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = 'row_id';
    const OPTION_ID = 'option_id';
    const STORE_ID = 'store_id';
    const LABEL = 'label';
    const DEFAULT_LABEL = 'label_0';
    const URL_KEY = 'url_key';
    const IMAGE = 'image';
    const DESCRIPTION = 'description';
    const LISTING = 'listing';
    const ATTRIBUTE = 'attribute';
    const ATTRIBUTE_ID = 'attribute_id';
    const STATUS = 'status';
    const INCLUDE_IN_WIDGET = 'include_in_widget';
    const WIDGET_LOGO = 'widget_logo';
    const META_TITLE = 'meta_title';
    const META_DESC = 'meta_desc';
    const POSITION = 'position';
    const SORT_ORDER = 'sort_order';
    const CMS_BLOCK = 'cms_block';
    const LAYOUT_UPDATE = 'layout_update';
    const LAYOUT_UPDATE_XML = 'layout_update_xml';
    const CREATED_AT = 'aa_option_created_at';
    const UPDATED_AT = 'aa_option_updated_at';

    /**#@-*/

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * Get option id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set option id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get option id
     *
     * @return int|null
     */
    public function getOptionId();

    /**
     * Set option id
     *
     * @param int $optionId
     * @return $this
     */
    public function setOptionId($optionId);

    /**
     * Get store id
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get label
     *
     * @return string|null
     */
    public function getLabel();

    /**
     * Set label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * Get default label
     *
     * @return string|null
     */
    public function getDefaultLabel();

    /**
     * Set default label
     *
     * @param string $label
     * @return $this
     */
    public function setDefaultLabel($label);

    /**
     * Get url key
     *
     * @return string|null
     */
    public function getUrlKey();

    /**
     * Set url key
     *
     * @param string $urlKey
     * @return $this
     */
    public function setUrlKey($urlKey);

    /**
     * Get direct url
     *
     * @return string
     */
    public function getUrl();

    /**
     * Get image
     *
     * @return string|null
     */
    public function getImage();

    /**
     * Set image
     *
     * @param string $image
     * @return $this
     */
    public function setImage($image);

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get listing enabled
     *
     * @return int|null
     */
    public function getListing();

    /**
     * Set listing enabled
     *
     * @param int $listing
     * @return $this
     */
    public function setListing($listing);

    /**
     * Get attribute
     *
     * @return \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface|null
     */
    public function getAttribute();

    /**
     * Get attribute
     *
     * @param \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface $aa
     * @return $this
     */
    public function setAttribute(\Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface $aa);

    /**
     * Get attribute id
     *
     * @return int|null
     */
    public function getAttributeId();

    /**
     * Set attribute id
     *
     * @param int $id
     * @return $this
     */
    public function setAttributeId($id);

    /**
     * Get status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get include in widget
     *
     * @return int|null
     */
    public function getIncludeInWidget();

    /**
     * Set include in widget
     *
     * @param int $includeInWidget
     * @return $this
     */
    public function setIncludeInWidget($includeInWidget);

    /**
     * Get widget logo
     *
     * @return string|null
     */
    public function getWidgetLogo();

    /**
     * Set widget logo
     *
     * @param string $widgetLogo
     * @return $this
     */
    public function setWidgetLogo($widgetLogo);

    /**
     * Get meta title
     *
     * @return string|null
     */
    public function getMetaTitle();

    /**
     * Set meta title
     *
     * @param string $metaTitle
     * @return $this
     */
    public function setMetaTitle($metaTitle);

    /**
     * Get meta description
     *
     * @return string|null
     */
    public function getMetaDesc();

    /**
     * Set meta description
     *
     * @param string $metaDesc
     * @return $this
     */
    public function setMetaDesc($metaDesc);

    /**
     * Get default position
     *
     * @return int|null
     */
    public function getPosition();

    /**
     * Set default position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position);

    /**
     * Get storefront sort order
     *
     * @return int|null
     */
    public function getSortOrder();

    /**
     * Set storefront sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * Get CMS Block
     *
     * @return int|null
     */
    public function getCmsBlock();

    /**
     * Set CMS Block
     *
     * @param int $blockId
     * @return $this
     */
    public function setCmsBlock($blockId);

    /**
     * Get layout
     *
     * @return string|null
     */
    public function getLayoutUpdate();

    /**
     * Set layout
     *
     * @param string $layout
     * @return $this
     */
    public function setLayoutUpdate($layout);

    /**
     * Get layout update xml
     *
     * @return string|null
     */
    public function getLayoutUpdateXml();

    /**
     * Set layout update xml
     *
     * @param string $layoutXml
     * @return $this
     */
    public function setLayoutUpdateXml($layoutXml);

    /**
     * @return string
     */
    public function getAaOptionUpdatedAt();

    /**
     * @return string
     */
    public function getAaOptionCreatedAt();

    /**
     * @param string $updatedAt
     * @return string
     */
    public function setAaOptionUpdatedAt($updatedAt);

    /**
     * @param string $createdAt
     * @return string
     */
    public function setAaOptionCreatedAt($createdAt);
}
