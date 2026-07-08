<?php

namespace Digidirect\Feed\Api\Data;

interface RuleInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const RULE_ID = 'rule_id';
    const NAME = 'name';
    const TYPE = 'type';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';
    const ACTIONS_SERIALIZED = 'actions_serialized';
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
     * @return \Digidirect\Feed\Api\Data\FeedInterface
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
     * @return \Digidirect\Feed\Api\Data\FeedInterface
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
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setType($type);

    /**
     * Get conditions serialized
     *
     * @return string
     */
    public function getConditionsSerialized();

    /**
     * Set conditions serialized
     *
     * @param string $conditionsSerialized
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setConditionsSerialized($conditionsSerialized);

    /**
     * Get actions serialized
     *
     * @return string
     */
    public function getActionsSerialized();

    /**
     * Set actions serialized
     *
     * @param string $actionsSerialized
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setActionsSerialized($actionsSerialized);

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
     * @return \Digidirect\Feed\Api\Data\FeedInterface
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
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setUpdatedAt($updatedAt);
}
