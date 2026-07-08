<?php
namespace Digidirect\AbstractEntity\Api\Data;

interface AbstractEntityInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    const NAME = 'name';
    const ATTRIBUTE_SET_ID = 'attribute_set_id';
    const STATUS = 'status';
    const VISIBLE_ON_FRONTEND = 'visible_on_frontend';
    const URL_KEY = 'url_key';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const STORE_ID = 'store_id';
    const PARENT_ID = 'parent_id';
    const ENTITY_ID = 'entity_id';
    const ADD_TO_SITEMAP = 'add_to_sitemap';

    const ATTRIBUTES = [
        self::NAME,
        self::ATTRIBUTE_SET_ID,
        self::STATUS,
        self::VISIBLE_ON_FRONTEND,
        self::URL_KEY,
        self::CREATED_AT,
        self::UPDATED_AT,
        self::STORE_ID,
        self::PARENT_ID,
        self::ENTITY_ID,
        self::ADD_TO_SITEMAP,
    ];

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface
     */
    public function setName($name);

    /**
     * Get attribute set id
     * @return string|null
     */
    public function getAttributeSetId();

    /**
     * Set attribute set id
     * @param int $setId
     * @return \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface
     */
    public function setAttributeSetId($setId);

    /**
     * Get status
     * @return int|null
     */
    public function getStatus();

    /**
     * Set status
     * @param int $status
     * @return \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface
     */
    public function setStatus($status);

    /**
     * Is visible on frontend
     * @return bool|null
     */
    public function isVisibleOnFrontend();

    /**
     * Set is visible on frontend
     * @param bool $visibleOnFrontend
     * @return \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface
     */
    public function setVisibleOnFrontend($visibleOnFrontend);

    /**
     * Get url key
     * @return string|null
     */
    public function getUrlKey();

    /**
     * Set url key
     * @param string $urlKey
     * @return \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface
     */
    public function setUrlKey($urlKey);

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return string|null
     */
    public function getEntityName();

    /**
     * @return mixed
     */
    public function getParentId();

    /**
     * @param int $id
     * @return mixed
     */
    public function setParentId($id);
}
