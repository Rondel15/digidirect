<?php

namespace Digidirect\Feed\Api;

interface CronHelperInterface
{
    /**
     * Method allows to display message about not working cron job in admin panel.
     * Need call at start of adminhtml controller action.
     *
     * @param string $jobCode Cron job code (from crontab.xml).
     * @param bool $output By default - return cron error as adminhtml error message, otherwise - as string.
     * @param string $prefix Additional text to cron job error message.
     * @return array [$status, $message]
     */
    public function checkCronStatus($jobCode, $output = true, $prefix = '');

    /**
     * Check if cron job is exists db table and executed less 6 hours ago
     *
     * @param string $jobCode
     * @return bool
     */
    public function isCronRunning($jobCode);
}
