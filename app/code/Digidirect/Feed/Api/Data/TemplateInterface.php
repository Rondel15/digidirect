<?php

namespace Digidirect\Feed\Api\Data;

interface TemplateInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const TEMPLATE_ID = 'template_id';
    const NAME = 'name';
    const TYPE = 'type';
    const FORMAT_SERIALIZED = 'format_serialized';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Digidirect\Feed\Api\Data\TemplateInterface
     */
    public function setId($id);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Set name
     *
     * @param string $name
     * @return \Digidirect\Feed\Api\Data\TemplateInterface
     */
    public function setName($name);

    /**
     * Get type
     *
     * @return string
     */
    public function getType();

    /**
     * Set type
     *
     * @param string $type
     * @return \Digidirect\Feed\Api\Data\TemplateInterface
     */
    public function setType($type);

    /**
     * Get format serialized
     *
     * @return string
     */
    public function getFormatSerialized();

    /**
     * Set format serialized
     *
     * @param string $formatSerialized
     * @return \Digidirect\Feed\Api\Data\TemplateInterface
     */
    public function setFormatSerialized($formatSerialized);

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return \Digidirect\Feed\Api\Data\TemplateInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return \Digidirect\Feed\Api\Data\TemplateInterface
     */
    public function setUpdatedAt($updatedAt);
}
