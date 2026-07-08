<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\Service\ErrorProcessing;

use Digidirect\AI\Model\Logger\LoggerInterface;
use Digidirect\AI\Model\Logger\Logger;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorFactory;

class ProcessingErrorAggregator extends \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator
{
    /**
     * @var Logger
     */
    protected $aiLogger;

    /**
     * @var string
     */
    protected $aiLogPlace;

    /**
     * @param \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorFactory $errorFactory
     * @param \Digidirect\AI\Model\Logger\LoggerInterface $aiLogger
     * @param string $aiLogPlace
     */
    public function __construct(
        ProcessingErrorFactory $errorFactory,
        LoggerInterface $aiLogger,
        $aiLogPlace = Logger::LOG_PLACE_FILE_AND_DB
    ) {
        parent::__construct($errorFactory);
        $this->aiLogger = $aiLogger;
        $this->aiLogPlace = $aiLogPlace;
    }

    /**
     * @param string $errorCode
     * @param string $errorLevel
     * @param int|null $rowNumber
     * @param string|null $columnName
     * @param string|null $errorMessage
     * @param string|null $errorDescription
     * @return $this
     */
    public function addError(
        $errorCode,
        $errorLevel = ProcessingError::ERROR_LEVEL_CRITICAL,
        $rowNumber = null,
        $columnName = null,
        $errorMessage = null,
        $errorDescription = null
    ) {
        $this->aiLogger->log(
            $errorLevel,
            __('Error on the line "%1". Skipped.', $rowNumber),
            [
                'errorCode' => $errorCode,
                'errorLevel' => $errorLevel,
                'rowNumber' => $rowNumber,
                'columnName' => $columnName,
                'errorMessage' => $errorMessage,
                'errorDescription' => $errorDescription,
            ],
            $this->aiLogPlace
        );
        return parent::addError($errorCode, $errorLevel, $rowNumber, $columnName, $errorMessage, $errorDescription);
    }

    /**
     * @param int $rowNumber
     * @return $this
     */
    public function addRowToSkip($rowNumber)
    {
        $this->aiLogger->log(
            ProcessingError::ERROR_LEVEL_WARNING,
            __('The line "%1" is skipped.', $rowNumber),
            [
                'rowNumber' => $rowNumber,
            ],
            $this->aiLogPlace
        );
        return parent::addRowToSkip($rowNumber);
    }

    /**
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->aiLogger = $logger;
        return $this;
    }

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        $errors = [];
        foreach ($this->getAllErrors() as $error) {
            $errors[] = $error->getErrorMessage();
        }
        return $errors;
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
