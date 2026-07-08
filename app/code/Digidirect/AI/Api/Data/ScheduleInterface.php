<?php

namespace Digidirect\AI\Api\Data;

/**
 * Interface ScheduleInterface
 *
 * @package Digidirect\AI\Api\Data
 * @api
 */
interface ScheduleInterface
{
    const PROCESS_CODE = 'process_code';
    const RUN_OPTIONS = 'run_options';
    const CREATED = 'created';
    const COMMENT = 'comment';
    const IS_ADDED_BY_ADMIN = 'is_added_by_admin';

    /**
     * @return string
     */
    public function getProcessCode();

    /**
     * @param string $value
     * @return $this
     */
    public function setProcessCode($value);

    /**
     * @return array
     */
    public function getRunOptions();

    /**
     * @param string|array $value
     * @return $this
     */
    public function setRunOptions($value);

    /**
     * @return string
     */
    public function getCreated();

    /**
     * @param string $value
     * @return $this
     */
    public function setCreated($value);

    /**
     * @return string
     */
    public function getComment();

    /**
     * @param string $value
     * @return $this
     */
    public function setComment($value);

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsAddedByAdmin();

    /**
     * @param bool $value
     * @return $this
     */
    public function setIsAddedByAdmin($value);
}
