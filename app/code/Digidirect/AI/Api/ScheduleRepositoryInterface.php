<?php

namespace Digidirect\AI\Api;

use Digidirect\AI\Api\Data\ScheduleInterface;
use Digidirect\AI\Api\Data\ScheduleSearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface ScheduleRepositoryInterface
 *
 * @package Digidirect\AI\Api
 * @api
 */
interface ScheduleRepositoryInterface
{
    /**
     * @param ScheduleInterface $schedule
     * @return ScheduleInterface
     */
    public function save(ScheduleInterface $schedule);

    /**
     * @param integer $id
     * @return ScheduleInterface
     */
    public function getById($id);

    /**
     * @param SearchCriteriaInterface $criteria
     * @return ScheduleSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * @param Data\ScheduleInterface $schedule
     * @return bool
     */
    public function delete(ScheduleInterface $schedule);

    /**
     * @param integer $eventId
     * @return bool
     */
    public function deleteById($eventId);
}
