<?php
namespace Digidirect\MyStoreWidget\Controller\MyStoreWidget;

use Magento\Framework\DataObject;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class SearchByAddress
 * @package Digidirect\MyStoreWidget\Controller\MyStoreWidget
 */
class SearchByAddress extends Index
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $responseData = [];
        $address = new DataObject($this->getRequest()->getParams());
        if ($store = $this->myStoreRepository->getStoreByAddress($address)) {
            $responseData = $store->getData();
        }

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;
    }
}
