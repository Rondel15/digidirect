<?php
namespace Digidirect\MyStoreWidget\Model\Customer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;

class SessionFactory extends \Magento\Customer\Model\SessionFactory
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * SessionFactory constructor.
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @param RequestInterface $request
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = CustomerSession::class,
        RequestInterface $request = null
    ) {
        parent::__construct($objectManager, $instanceName);
        $this->request = $request ?: $objectManager->get(RequestInterface::class);
    }

    /**
     * @param array $data
     * @return CustomerSession
     */
    public function create(array $data = [])
    {
        if ($this->request->getModuleName() == 'page_cache') {
            return parent::create($data);
        }
        return $this->_objectManager->get($this->_instanceName, $data);
    }
}
