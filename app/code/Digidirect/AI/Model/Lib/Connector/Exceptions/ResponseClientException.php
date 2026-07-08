<?php
namespace Digidirect\AI\Model\Lib\Connector\Exceptions;

/**
 * Class ResponseException
 *
 * @package Digidirect\AI\Model\Lib\Connector\Exceptions
 */
class ResponseClientException extends ConnectionException
{
    /**
     * Status
     *
     * @var int
     */
    protected $_status;

    /**
     * Error Code
     *
     * @var int
     */
    protected $_errorCode;

    /**
     * Error Description
     *
     * @var int
     */
    protected $_errorDescription;
    
    /**
     * ResponseException constructor.
     *
     * @param \Zend\Http\Response $response
     */
    public function __construct(\Zend\Http\Response $response)
    {
        parent::__construct($response->getReasonPhrase());
        $this->_status = $response->getStatusCode();
        $this->_errorCode = $response->getStatusCode();
        $this->_errorDescription = $response->getReasonPhrase();
    }

    /**
     * Get response status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Get error code
     *
     * @return int
     */
    public function getErrorCode()
    {
        return $this->_errorCode;
    }

    /**
     * Get error description
     *
     * @return int
     */
    public function getErrorDescription()
    {
        return $this->_errorDescription;
    }
}
