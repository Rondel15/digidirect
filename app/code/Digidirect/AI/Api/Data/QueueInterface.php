<?php
namespace Digidirect\AI\Api\Data;

/**
 * Queue interface.
 *
 * @api
 */
interface QueueInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const PROCESS_CODE = 'process_code';
    const INITIATOR = 'initiator';
    const PROCESS_DATA = 'process_data';
    const STATE = 'state';
    const STATUS = 'status';
    const IS_RETIRED = 'is_retired';
    const SEQUENCE = 'sequence';
    const RUN_NUMBER = 'run_number';
    const LAST_RUN_AT = 'last_run_at';
    const CREATED_AT = 'created_at';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get process code
     *
     * @return string|null
     */
    public function getProcessCode();

    /**
     * Get initiator
     *
     * @return string|null
     */
    public function getInitiator();

    /**
     * Get process data
     *
     * @return string|null
     */
    public function getProcessData();

    /**
     * Get state
     *
     * @return int|null
     */
    public function getState();

    /**
     * Get status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Get Is Retired
     *
     * @return bool
     */
    public function getIsRetired();

    /**
     * Get sequence
     *
     * @return string|null
     */
    public function getSequence();

    /**
     * Get run numbers
     *
     * @return int|null
     */
    public function getRunNumber();

    /**
     * Get Last run datetime
     *
     * @return string|null
     */
    public function getLastRunAt();

    /**
     * Get creation datetime
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set ID
     *
     * @param int $id
     * @return QueueInterface
     */
    public function setId($id);

    /**
     * Set process code
     *
     * @param string $processCode
     * @return QueueInterface
     */
    public function setProcessCode($processCode);

    /**
     * Set initiator
     *
     * @param string $initiator
     * @return QueueInterface
     */
    public function setInitiator($initiator);

    /**
     * Set process data
     *
     * @param string $processData
     * @return QueueInterface
     */
    public function setProcessData($processData);

    /**
     * Set state
     *
     * @param int $state
     * @return QueueInterface
     */
    public function setState($state);

    /**
     * Set status
     *
     * @param int $status
     * @return QueueInterface
     */
    public function setStatus($status);

    /**
     * Set Is Retired
     *
     * @param bool $isRetired
     * @return QueueInterface
     */
    public function setIsRetired($isRetired);

    /**
     * Set sequence
     *
     * @param string $sequence
     * @return QueueInterface
     */
    public function setSequence($sequence);

    /**
     * Set run numbers
     *
     * @param int $runNumber
     * @return QueueInterface
     */
    public function setRunNumber($runNumber);

    /**
     * Set last run datetime
     *
     * @param string $lastRunAt
     * @return QueueInterface
     */
    public function setLastRunAt($lastRunAt);

    /**
     * Set creation datetime
     *
     * @param string $createdAt
     * @return QueueInterface
     */
    public function setCreatedAt($createdAt);
}
