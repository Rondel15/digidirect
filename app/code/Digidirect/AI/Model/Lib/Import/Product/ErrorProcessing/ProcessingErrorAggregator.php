<?php
namespace Digidirect\AI\Model\Lib\Import\Product\ErrorProcessing;

use \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator as ErrorAggregator;

/**
 * Class ProcessingErrorAggregator
 *
 * @package Digidirect\AI\Model\Lib\Import\Product\ErrorProcessing
 */
class ProcessingErrorAggregator extends ErrorAggregator
{
    /**
     * Is error already added
     * @param int $rowNum
     * @param string $errorCode
     * @param string $columnName
     * @return bool
     */
    protected function isErrorAlreadyAdded($rowNum, $errorCode, $columnName = null)
    {
        return false;       // it has to return false to get full errors stack
    }

    /**
     * Rewrite for base method
     *
     * @return bool
     */
    public function hasToBeTerminated()
    {
        return $this->isErrorLimitExceeded();
    }
}
