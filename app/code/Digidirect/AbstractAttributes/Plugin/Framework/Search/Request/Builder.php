<?php
namespace Digidirect\AbstractAttributes\Plugin\Framework\Search\Request;

use Digidirect\AbstractAttributes\Model\Search\RequestGenerator;
use Magento\Framework\App\Request\Http as HttpRequest;

class Builder
{
    /**
     * @var HttpRequest
     */
    protected $_request;

    /**
     * @param HttpRequest $request
     */
    public function __construct(
        HttpRequest $request
    ) {
        $this->_request = $request;
    }

    /**
     * @param \Magento\Framework\Search\Request\Builder $subject
     * @param string $requestName
     * @return array
     */
    public function beforeSetRequestName(
        \Magento\Framework\Search\Request\Builder $subject,
        $requestName
    ) {
        if ($this->_request->getModuleName() == 'eaa') {
            $requestName = RequestGenerator::EAA_OPTION_REQUEST_NAME;
        }
        return [$requestName];
    }
}
