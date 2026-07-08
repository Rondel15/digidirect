<?php

namespace Digidirect\MyStoreWidget\Controller\MyStoreWidget;

use Digidirect\MyStoreWidget\Api\MyStoreRepositoryInterface;
use Digidirect\MyStoreWidget\Model\MyStoreFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\InputException;

/**
 * Class Search
 *
 * @package Digidirect\MyStoreWidget\Controller\MyStoreWidget
 */
class Search extends Index
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Search constructor.
     *
     * @param Context $context
     * @param MyStoreRepositoryInterface $myStoreRepository
     * @param MyStoreFactory $myStoreFactory
     * @param Session $customerSession
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        MyStoreRepositoryInterface $myStoreRepository,
        MyStoreFactory $myStoreFactory,
        Session $customerSession,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context, $myStoreRepository, $myStoreFactory, $customerSession);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $storeName = $this->getRequest()->getParam('store_name', '');
        $result = [];

        try {
            $items = $this->myStoreRepository->getStoresCollection($storeName);
            $result['items'] = $items;
        } catch (InputException $e) {
            $result['error'] = $e->getMessage();
        } catch (\Exception $e) {
            $result['error'] = __('Something went wrong.');
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
