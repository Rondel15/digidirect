<?php
namespace Digidirect\MyStoreWidget\Api\Data;

/**
 * Interface MyStoreInterface
 * @package Digidirect\MyStoreWidget\Api\Data
 */
interface MyStoreInterface
{
    const ID = 'id';
    const CUSTOMER_ID = 'customer_id';
    const ABSTRACT_ENTITY_ID = 'abstract_entity_id';
    const SEARCH_TEXT = 'search_text';
    const TYPE = 'type';

    const DEFAULT_TYPE = '';

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return mixed
     */
    public function getCustomerId();

    /**
     * @return mixed
     */
    public function getAbstractEntityId();

    /**
     * @param int $customerId
     * @return mixed
     */
    public function setCustomerId($customerId);

    /**
     * @param int $entityId
     * @return mixed
     */
    public function setAbstractEntityId($entityId);

    /**
     * @param int $id
     * @return mixed
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getSearchText();

    /**
     * @param string $text
     * @return mixed
     */
    public function setSearchText($text);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $text
     * @return mixed
     */
    public function setType($text);
}
