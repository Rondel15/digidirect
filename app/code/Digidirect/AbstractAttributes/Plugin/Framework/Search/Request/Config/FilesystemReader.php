<?php
namespace Digidirect\AbstractAttributes\Plugin\Framework\Search\Request\Config;

use Digidirect\AbstractAttributes\Model\Search\RequestGenerator;

class FilesystemReader
{
    /**
     * @var RequestGenerator
     */
    protected $_requestGenerator;

    /**
     * FilesystemReader constructor.
     * @param RequestGenerator $requestGenerator
     */
    public function __construct(
        RequestGenerator $requestGenerator
    ) {
        $this->_requestGenerator = $requestGenerator;
    }

    /**
     * @param \Magento\Framework\Config\ReaderInterface $subject
     * @param array $result
     * @return array
     */
    public function afterRead(
        \Magento\Framework\Config\ReaderInterface $subject,
        array $result
    ) {
        if (isset($result[RequestGenerator::EAA_OPTION_REQUEST_NAME])) {
            $result = array_merge_recursive($result, $this->_requestGenerator->generate());
        }
        return $result;
    }
}
